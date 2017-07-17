# Piper

Piper is a idea for an application framework composed from loosely coupled services or functions called here Pipes.

## Pipe and Pipeline in theory

Pipe is a function that take one input object and returns one output object, optionally output can be null. 
This limitation makes it possible to connect many Pipes together creating Pipeline.

Pipeline is initialized as collection of pipes and its flow depends on input and how pipes connects to each other.
Pipeline gets the input, finds matching pipe and passes input through. 
Than it takes the output and continues the same process until there is no pipe matching last output object.
This last not processed object can be called pipeline rest or pipeline result. 

Simple pipeline example:
```
// Pipeline composed from given pipes:
p1(A): B (p1 takes A as input and outputs B)
p2(B): C
p3(C): D
p4(X): B

// Pipeline for input A
A -> p1(A) -> B -> p2(B) -> C -> p3(C) -> D

// Pipeline for input X
X -> p4(X) -> B -> p2(B) -> C -> p3(C) -> D
```

`D` is the rest from pipeline and can be optionally passed to rest handler callback.

Pipeline can contain many pipes matching same input, each with defined order.
Pipes matching same input are called one after another until input is transformed to different type.

The pipeline follows the logic:
1. Get the pipes that match provided input and put them in order.
2. Let the input into first pipe and then:
   * if pipe output is different than input (transforming pipe), start again with output as input,
   * if pipe output is same type as input (flow pipe), continue with next pipe using output,
   * if pipe is clogged, continue with next pipe using same input.
3. When there are no pipes matching last output, pass it to provided rest handler.

#### Transforming pipe

Kind of pipe that transforms input into object of different type.

The most common example is http action which takes request on input and outputs response:
```php
<?php

interface HttpAction
{
    public function invoke(ServerRequestInterface $request): ResponseInterface;
}
```

Transforming pipe is the one that pushes pipeline forward. 
The simplest pipeline can be composed from pipes where each next pipe input matches previous pipe output.

#### Flow pipe

Kind of pipe where input and output has the same type.

For example request attribute converter could look line this:
```php
<?php

use Psr\Http\Message\ServerRequestInterface;
use Domain\User\UserId;

final class UserIdConverter
{
    public function convert(ServerRequestInterface $request): ServerRequestInterface
    {
        $userId = $request->getAttribute('userId', null);
        
        if ($userId !== null) {
            $request = $request->withAttribute('userId', UserId::from($userId));
        }
        
        return $request;
    }
}
```

#### Clogged pipe

Kind of pipe that does not produce output, probably due certain conditions.

Example:
```php
<?php

final class ActionFilter
{
    public function mustBeAuthorized(ServerRequestInterface $request): ?ResponseInterface
    {
        $session = $request->getAttribute('session', Session::anonymous());
        
        if ($session->isAuthorized()) {
            return new Response('DENIED', 404);
        }
        
        return null;
    }
}

final class AuthorizedAction
{
    public function invoke(ServerRequestInterface $request): ResponseInterface
    {
        return new Response('OK', 200);
    }
}
```

`ActionFilter` must be called before `AuthorizedAction`, so it is defined with earlier order.

Returning null is maybe not the best pattern, perhaps better would be something like `Either<ServerRequestInterface, ResponseInterface>`,
but unfortunately PHP does not support generics yet.

### Object tags

Pipe configuration must contain definition of what kind of object can be handled. 
It should be defined precisely to avoid unexpected pipeline behaviour.

Pipe can declare that it accepts only instances of given class or interface, additionally it can declare that it accepts only instances with given set of public attributes.
  
For example `new ObjectTag(Example::class, ['group' => 'simple'])` tells that pipe accepts only instances of `Example` class that meets the condition `$example->group() === 'simple'`.

Extracting tags from object is done by `ObjectTagger`. 
Default implementation extracts only class and interfaces, attributes tags requires specific implementation.
