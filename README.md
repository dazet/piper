# Piper

Piper is the idea for creating application as a sequence of loosely coupled functions called Pipes.

## Pipe and Pipelines

Pipe here is a function that takes one object and outputs one object or have no output. 

Pipeline is constructed from pipes, its flow depends on initial input and how pipes connects to each other.

The Pipeline follows the logic:
1. Find Pipes that match initial input and put them in order.
2. Call first pipe with current input and get output:
   * if output is different than input, start again with output as input,
   * if output is the same type as input (flow pipe), continue with next pipe with output as current input,
   * if output is null, continue with next pipe using same input.
3. When there are no pipes matching last output, pass it to provided rest handler.

Pipeline can contain many pipes matching same input, each have defined priority.
Pipes matching same input are called one after another until input is transformed to different type.

#### Transforming pipe

Function that transforms input object into different type. Pipeline is pushed forward.

The most common example is http action which takes request and returns response:
```php
interface HttpAction
{
    public function handle(Request $request): Response;
}
```

The simplest pipeline can be composed from pipes where each next pipe input matches previous pipe output.

#### Flow pipe

Function where input and output has the same type. Pipeline continues with replaced input.

For example request attribute converter could look line this:
```php
final class UserIdConverter
{
    public function convert(Request $request): Response
    {
        $userId = $request->getAttribute('userId', null);
        
        if ($userId !== null) {
            $request = $request->withAttribute('userId', UserId::from($userId));
        }
        
        return $request;
    }
}
```

It can be used to task like: parameter conversion, data normalizing etc.

#### Clogged pipe

Function that does not return anything. Pipelne will continue with the same input.

Example:
```php
<?php

final class ActionFilter
{
    public function mustBeAuthorized(Request $request): ?Response
    {
        $session = $request->getAttribute('session', Session::anonymous());
        
        if ($session->isAuthorized()) {
            return new Response('DENIED', 403);
        }
        
        return null;
    }
}

final class AuthorizedAction
{
    public function invoke(Request $request): Response
    {
        return new Response('OK', 200);
    }
}
```

`ActionFilter` must be called before `AuthorizedAction`, so it is defined as a Pipe with earlier order.

Returning null is maybe not the best pattern, perhaps better would be something like `Either<Request, Response>`, but unfortunately PHP does not support generics yet.

### Object tags

Pipe configuration must contain definition of what kind of object it is able to handle. 
It should be defined precisely to avoid unexpected pipeline behaviour.

Pipe can declare that it accepts only instances of given class or interface, additionally it can require instances with given set of attributes.

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
    //...
}

// Pipe 1: Build the request
class RequestFactory
{
    public function __invoke(CreateRequest $command): Request
    {
        // build Request form command or from globals
        // ...
        return $request;
    }
}

// Pipe definition for RequestFactory
$requestFactory = new CallablePipe(
    new RequestFactory(),                      // Pipe callback
    ObjectTags::forClass(CreateRequest::class) // Pipe input definition
);

// Pipe 2: Find route matching request uri and append to request
class Router
{
    public function __invoke(Request $request): Response
    {
        // find the route and add to request attributes
        // ...
        return $request->withAttribute('route', $route); 
    }
}

// Pipe definition for Router, it has order -10 to be called before other pipes
$router = new CallablePipe(
    new Router(),
    ObjectTags::forClass(Request::class),
    -10 // call it before the others
);

// Pipe 3: Controller action
class IndexAction
{
    public function handle(Request $request): Response
    {
        return new Response('This is index'); 
    }
}

// Pipe definition for request routed to index
$indexAction = new CallablePipe(
    new IndexAction(),
    // accept only request with route `index`
    ObjectTags::forClass(Request::class, ['route' => 'index'])
);

class HelloAction
{
    public function handle(Request $request): Response
    {
        $name = $request->getAttribute('name', 'stranger');
        
        return new Response("Hello {$name}!"); 
    }
}

// Pipe definition for request routed to hello
$helloAction = new CallablePipe(
    new HelloAction(), 
    ObjectTags::forClass(Request::class, ['route' => 'hello'])
);

// Action returning 404 page when route is not found
class NotFoundAction
{
    public function handle(Request $request): Response
    {
        return new Response('Page not found', 404); 
    }
}

// Pipe definition for not found action,
// it has order 10 to be called only when no other action return response
$notFoundAction = new CallablePipe(
    new NotFoundAction(), 
    ObjectTags::forClass(Request::class),
    10
);

// Emit response and dispatch ResponseEmitted
class ResponseEmitted
{
}

class ResponseEmitter
{
    public function __invoke(Response $response): ResponseEmitted
    {
        // echo response
        // ...
        return new ResponseEmitted(); 
    }
}

// Pipe definition for ResponseEmitter
$responseEmitter = new CallablePipe(
    new ResponseEmitter(), 
    ObjectTags::forClass(Response::class)
);

// Now let`s create pipeline
$objectTagger = TaggersAggregate::default(
    // cusom tagger that is able to tag routed request 
    new RoutedRequestTagger() 
);

$pipeline = new Pipeline(
    $objectTagger, // ObjectTagger
    $requestFactory, // Pipe ...$pipes
    $router, 
    $indexAction, 
    $helloAction, 
    $notFoundAction, 
    $responseEmitter
);

// run pipelne
$pipeline->pump(new CreateRequest());

```

## Creating pipelines with `PipelineBuilder`

`PipelineBuilder` gives simplified way to create pipelines from functions or any type of callables.

#### Pipeline from anonymous functions:

```php
$pipeline = PipelineBuilder::new()
    ->pipe(
        function (A $a): B {
            return new B();
        },
        function (B $b): C {
            return new C();
        }
    )
    ->build();

// same as:
$pipeline = PipelineBuilder::new()
    ->pipe(function (A $a): B {
        return new B();
    })
    ->pipe(function (B $b): C {
        return new C();
    })
    ->build();

$pipeline->pump(new A()); // returns C
```

#### Pipeline from anonymous functions with custom defined input types:

```php
$pipeline = $this
    ->pipeFor(A::class, function ($a): B {
        return new B();
    })
    ->pipeFor(B::class, function ($b): C {
        return new C();
    })
    ->build();

$pipeline->pump(new A()); // returns C
```

