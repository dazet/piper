# Piper

Piper is a idea for an application architecture where everything is a pipe.

## What is a Pipe?

Pipe is kind of function that takes one input object and returns one output object or null (clogged pipe).

#### Redirecting pipe

Kind of pipe that transforms input into something else.

Example:
```php
<?php

interface HttpAction
{
    public function invoke(ServerRequestInterface $request): ResponseInterface;
}
```

#### Transforming pipe

Kind of pipe returns the same object or modified object of the same type.

Example:
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

`ActionFilter` obviously must be called before `AuthorizedAction`, but we let`s assume that bot actions belongs to the same pipeline...

## Pipeline

Pipeline is a set of pipes combined together that can handle input of given type and returns expected output.

The pipeline follows the path:
1. Get pipes that matches provided input.
2. Let the input into the first pipe and then:
   * if pipe output is different object than input (transforming pipe), get the pipes that matches output and continue,
   * if pipe output is same type as input (filtering pipe), continue with next pipe using output,
   * if pipe is clogged, continue with next pipe using same input.
3. When there are no pipes matching last output, pass it to provided output handler.

The order of pipe flow is defined by `Pipe` `order` attribute.

## Object tags

Pipe input and output matching depends on the tags that can be assigned to objects.

Object can have assigned following tags:
* `Object is instance of Class`
* `Object is instance of Interface`
* `Object is instance of Class with given sets off attributes`
