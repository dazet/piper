# Piper

Piper is a idea for an application framework composed from loosely coupled services or functions called here Pipes.

Currently this project is a draft, not yet production ready.

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

Pipe can declare that it accepts only instances of given class or interface, additionally it can require instances with given set of public attributes.
  
For example `new ObjectTag(Example::class, ['group' => 'simple'])` defines that pipe accepts only instances of `Example` class that meets the condition `$example->group() === 'simple'`.

Extracting tags from object is done by `ObjectTagger`. 
Default implementation extracts only class and interfaces from object, attributes tags requires specific implementation.

## Creating pipeline from code

First we need to define some services that can be used as pipes.
Let`s take http request example.

```php
<?php

// Command send to pipeline to initialize process
class CreateRequest
{
}

use Psr\Http\Message\ServerRequestInterface;

// Build the request
class RequestFactory
{
    public function __invoke(CreateRequest $command): ServerRequestInterface
    {
        // ...
        return $request;
    }
}

use Piper\Pipe\CallablePipe;
use Piper\Pipe\ObjectTags;

// Pipe definition for RequestFactory
$requestFactory = new CallablePipe(new RequestFactory(), ObjectTags::fromClass(CreateRequest::class));

// Find route matching request uri and append to request
class Router
{
    public function __invoke(ServerRequestInterface $request): ServerRequestInterface
    {
        // ... find the route ...
        return $request->withAttribute('route', $route); 
    }
}

// Pipe definition for Router, it has order -10 to be called before other pipes
$router = new CallablePipe(new Router(), ObjectTags::fromClass(ServerRequestInterface::class), -10);

use Psr\Http\Message\ResponseInterface;

// Some controller actions
class IndexAction
{
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return new Response('This is index'); 
    }
}

// Pipe definition for request routed to index
$indexAction = new CallablePipe(
    new IndexAction(), 
    ObjectTags::fromClass(ServerRequestInterface::class, ['route' => 'index'])
);

class HelloAction
{
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $name = $request->getAttribute('name', 'stranger');
        
        return new Response("Hello {$name}!"); 
    }
}

// Pipe definition for request routed to hello
$helloAction = new CallablePipe(
    new HelloAction(), 
    ObjectTags::fromClass(ServerRequestInterface::class, ['route' => 'hello'])
);

// Action returning 404 page when route is not found
class NotFoundAction
{
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return new Response('Page not found', 404); 
    }
}

// Pipe definition for not found action,
// it has order 10 to be called only when no other action return response
$notFoundAction = new CallablePipe(
    new NotFoundAction(), 
    ObjectTags::fromClass(ServerRequestInterface::class),
    10
);

// Emit response and dispatch ResponseEmitted
class ResponseEmitted
{
}

class ResponseEmitter
{
    public function __invoke(ResponseInterface $response): ResponseEmitted
    {
        // ... some dirty work here ...
        return new ResponseEmitted(); 
    }
}

// Pipe definition for ResponseEmitter
$responseEmitter = new CallablePipe(new ResponseEmitter(), ObjectTags::fromClass(ResponseInterface::class));

// Now let`s create pipeline
use Piper\Pipe\ObjectTagger\TaggersAggregate;
use Piper\Pipe\ObjectTagger\ClassTagger;
use Piper\Pipe\ObjectTagger\InterfacesTagger;
use Piper\Http\Routing\RoutedRequestTagger;

$objectTagger = new TaggersAggregate(new ClassTagger(), new InterfacesTagger(), new RoutedRequestTagger());

use Piper\Pipeline;

$pipeline = new Pipeline(
    $objectTagger, 
    $requestFactory, 
    $router, 
    $indexAction, 
    $helloAction, 
    $notFoundAction, 
    $responseEmitter
);

// Not pipeline is ready to handle requests
$pipeline->pump(new CreateRequest());

```
