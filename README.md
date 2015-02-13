Lumy 2.8.0
==========

Lumy is a [minimal](https://en.wikipedia.org/wiki/Minimalism_(computing)) micro CLI/HTTP framework that aims to be quick, effective and simple to extend as you want with any external component. It is heavily based on [Chernozem](https://github.com/pyrsmk/Chernozem) to provide an efficient way for dependencies injection.

Lumy is born with the fact that micro-frameworks, like [Silex](http://silex.sensiolabs.org/) or [Slim](http://slimframework.com/), are still too big and handle behaviors that can be managed by many existing libraries. They're often too web-oriented too : PHP is also great with CLI environment.

It is shipped with a CLI/HTTP router, environment objects and a middleware stack.

Install
-------

Pick up the source or install it with [Composer](https://getcomposer.org/) :

```json
{
    "require": {
        "pyrsmk/lumy": "2.8.*"
    }
}
```

If you're installing it with Composer, you just need to import that Composer's autoloader :

```php
require 'vendor/autoload.php';
```

If not, you'll need to set up an autoloader to load Lumy by yourself.

A quick example
---------------

```php
$lumy=new Lumy\Http;

// Add a middleware to configure our template engine (with [Twig](http://twig.sensiolabs.org/))
$lumy->middleware(function($middlewares) use($lumy){
    $lumy['twig']=new Twig_Environment(
        new Twig_Loader_Filesystem($lumy['dirs']['templates']),
        $options
    );
    $middlewares->next();
});

// Add a basic route that do nothing but display our index page
$lumy->get('/',function() use($lumy){
    // Provide a rooturi variable to the template is really useful for CSS, images, scripts inclusion
    echo $lumy['twig']->render('index.tpl',array(
        'rooturi' => $lumy['environment']->getRootUri()
    ));
});

// Specify an error handler, throwed when an exception has been catched
$lumy->error(function($e) use($lumy){
    echo $lumy['twig']->render('error.tpl',array(
        'rooturi' => $lumy['environment']->getRootUri(),
        'message' => $e->getMessage()
    ));
});

// Run the application and print the response body
echo $lumy->run();
```

Basics
------

The Lumy object is a singleton and can be retrieved/instantiated with :

```php
$lumy=Lumy\Http::getInstance();
```

### Adding CLI routes

The basic way to add a route in a CLI environment is :

```php
$lumy->route('--help',function(){
    // Will print 'Some help' when the 'your_app --help' command is called
    echo 'Some help';
});
```

We can specify an array of route chains :

```php
$chains=array('--help','-h');
$lumy->route($chains,function(){
    echo 'Some help';
});
```

Since the routing system is based on [LongueVue](https://github.com/pyrsmk/LongueVue), we can use slugs to extract options :

```php
$lumy->route('install {directory}',function($directory){
    // Some actions
});
```

There're cases where we need to verify if the command syntax is valid. We can achieve it by using regexes to validate the command. If the specified regex does not match the route neither :

```php
$lumy->route('remove user {id}',function($id){
    // Some actions
},array(
    'directory' => '\d+'
));
```

There're also some other cases where we want to have a default value from some of our slugs :

```php
// The 'show' command will show tables by default
$lumy->route('show {arg}',function($arg){
    // Some actions
},array(
    'arg' => 'databases|tables'
),array(
    'arg' => 'tables'
));
```

### Adding routes with HTTP environment

HTTP routes work the same as in CLI context but the `route()` method has been replaced in favor of `get()`, `post()`, `put()` and `delete()`. Here's some examples to clarify the situation :

```php
$lumy->get('/gallery',function(){
    // Display the gallery
});

$lumy->put('/gallery',function(){
    // Add a new picture
});

$lumy->delete('/gallery/{id}',function($id){
    // Delete the specified picture
});
```

Please note that `PUT` and `DELETE` requests work like the `POST` request. Their data will be available from the `$_POST[]` array. If you need to add a custom route, use the `map()` method :

```php
$lumy->map('SEARCH','/gallery/{terms}',function($terms){
    // Display the gallery
});
```

The HTTP context adds some new constants to ease the writing of routes :

- `%scheme%` : matches `http` and 'https'
- `%host%` : matches the current host
- `%requesturi%` : matches the request URI (it's the root URI plus the resource URI)
- `%rooturi%` : matches the root URI (everything between the host and the relative path of your website)
- `%resourceuri%` : matches the resourceuri URI (the specific URI that belongs to your website)

```php
$lumy->get('%scheme%://{user}.mywebsite.com/profile',function($user){
    // Show the requested user profile
},array(
    'user' => '\w+'
));
```

If needed, you can assemble an URL using a predefined route :

```php
// Name the route to register
$lumy->get('/gallery/{id}',function(){
    // Some actions
},null,null,'gallery');

// ...

// Assemble an URL to include to your page/template
// This will print '/gallery/72'
echo $lumy->assembleUrl('gallery',array('id'=>72));
```

As you may already guess, the HTTP context makes REST requests. But the `PUT` and `DELETE` requests are [not supported in HTML](https://programmers.stackexchange.com/questions/114156/why-are-there-are-no-put-and-delete-methods-on-html-forms). So, to know which request is sent, Lumy watch the `$_POST['_METHOD']` variable for the method to use. Add an `<input name="_METHOD" value="DELETE">` in your form. There's also a [library](https://github.com/pyrsmk/RIP) that simplifies the whole thing by making synchroneous REST requests on-the-fly.

### Registering custom values and services

Since Lumy is built on top of Chernozem (we advise you to read its [documentation](https://github.com/pyrsmk/Chernozem)), it supports variables containing and, more interesting, services :

```php
// Define the 'session' closure
$lumy['session']=function(){
    return new Session();
};
// Set the closure as a service
$lumy->service('session');
```

Now, when the service is retrieved, the `Session` object is automatically instantiated. It permits us to only load objects that we really want for the requested page to render.

```php
// The Session object is ready
$lumy['session']['auth']=true;
```

### Middlewares

In Lumy, we're using middlewares to modularize applications. Each middleware is a simple closure/callback that is called at run time one by one, in the order they're defined. Each middleware need to call the next middleware itself, it permits to wrap all middlewares in one function. When Lumy is run, a middleware is automatically created to run routing functions. Then, keep in mind that you can wrap the entire application with any middleware.

```php
$lumy->middleware(function($middlewares){
    // Define our session service
    $lumy['session']=function(){
        return new Session();
    };
    $lumy->service('session');
    // Call the next middleware
    $middlewares->next();
    // Clean up session when the application has been runned
    unset($lumy['session']['cache']);
});
```

### Handling errors

The error handler is defined by the `error()` method which takes a callback :

```php
$lumy->error(function($exception){
    // Print the encountered error
    echo $exception->getMessage();
});
```

### Run the application

When you're good you can :

```php
$lumy->run();
```

The request object
------------------

All request objects have a `getChain()` method that returns the whole request/command chain for the request. You can retrieve this object with `$lumy['request']`.

### CLI

The CLI request object is pretty concise. It implements two methods to deal with the command chain (the following examples are based on the `myapp install install/path/` command) :

- `getArguments()` : gets an array of arguments for the passed command (for example : `['install', 'install/path/']`)
- `getApplicationName()` : gets the application name (for example : `myapp`)

### HTTP

The HTTP request object implements several useful functions to deal with HTTP requests (the following examples are based on the `http://mywebsite.com/app/gallery/7` request) :

- `getScheme()` : get the scheme of the request, either `http` or `https`
- `getHost()` : get the host (example : `mywebsite.com`)
- `getPort()` : get the port for the request (example : `80`)
- `getRequestUri()` : get the request URI (the request chain relative to the current host) (example : `mywebsite.com`)
- `getRootUri()` : get the root URI (it's the path relative to your application/website for the request) (example : `/app`)
- `getResourceUri()` : get the resource URI (it's the path relative to the specific request for your application/website) (example : `/gallery/7`)
- `getMethod()` : get the method for the request (generally `GET`, `POST`, `PUT` or `DELETE`)
- `isAjax()` : true if it's an AJAX request
- `isFlash()` : true if it's an AMF request
- `isSecure()` : true if it's an HTTPS request
- `getClientIp()` : get the IP of the client

The response object
-------------------

Response objects implement 4 methods for managing the response body. You can retrieve this object with `$lumy['response']`.

It also implements a `__toString()` function to print directly the response object returned by Lumy when the application has runned :

```
echo $lumy->run();
```

Here's how we're managing the response body :

- `setBody($body)` : set the body for the response object
- `prependBody($body)` : prepend contents to the body
- `appendBody($body)` : append contents to the body
- `getBody()` : returns the body

### CLI

The CLI response object has methods for setting/getting environment variables and set ANSI colors :

- `setVariable($name,$value)` : set an environment variable
- `getVariable($name)` : get an environment variable
- `unsetVariable($name)` : unset a variable
- `colorize($color,$background,$style)` : return an ANSI color
- `reset()` : return the ANSI code to reset colors and styles

For convenience, this response object is shipped with constants for ANSI codes you can use with `colorize()` :

- colors : `BLACK`, `RED`, `GREEN`, `YELLOW`, `BLUE`, `PURPLE`, `CYAN`, `GREY`
- styles : `BOLD`, `DIM`, `UNSERLINE`, `BLINK`, `HILIGHT`, `HIDE`, `STROKE`

### HTTP

The HTTP response object can manage headers, status code and provide some useful functions to deal with the browser :

- `setHeader($name,$value)` : set a header
- `getHeader($name)` : get a header
- `unsetHeader($name)` : unset a header
- `setStatus($code)` : set the status code to send to the browser
- `getStatus()` : get the status code
- `redirect($url,$status)` : redirect to the specified absolute/relative URL; the status code sent by default is `302`
- `send($path)` : force the browser to send a download
- `display($path)` : force the browser to display a file (generally an image)

Advanced use of routes
----------------------

The routing mechanism is used internally and we generally don't have to bother about it, otherwise there's some cases where we want to deal with routes directly. For that, we should name our routes by passing a name as the last argument of `route()`, `get()`, `post()`, etc :

```php
// Add a 'gallery' route
$lumy->get('/gallery',function(){
    // Some actions
},null,null,'gallery');
```

Then you can get the route with `$lumy['router']['gallery']` and access to the following methods :

- `match($chain)` : match the specified chain against the route chain
- `getChain()` : return the route chain
- `getMatcher()` : return the matcher object used to verify if the route chain is valid against the current request (for more informations, see the [LongueVue](https://github.com/pyrsmk/LongueVue) project)
- `getController()` : return the registered controller with this route

License
-------

Lumy is published under the [MIT license](http://dreamysource.mit-license.org).
