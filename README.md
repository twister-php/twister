# Twister
> Twister is a fast and light-weight component library

## Skeleton App

There is a [skeleton application](https://github.com/twister-php/skeleton) based on this code!

## Container

At the heart of the library, sits a very fast, very flexible, simple and elegant Inversion-of-Control (IoC) Container.
In fact, there is no need for **global variables, define's, pipelines, Kernels or an App**, as demonstrated in the [skeleton app](https://github.com/twister-php/skeleton).

The Container controls the entire flow of code (except routing), with a custom `execute()` function (written by you);
    which is actually just an anonymous callback function inside the Container, called from the Front Controller (index.php);
    even the name of that function can be changed in the config file.
    ie. There is NO pre-programmed flow of the program or hard-coded Kernel/App.
      Most things are handled/registered with the IoC Container, objects are pre-configured and 'lazy-loaded' on request/use only!

The primary technique used is called 'inline factories' (on [PHP-DI](http://php-di.org/)) to create both singleton AND unique instances.

For example:

When you require something from the config files:
```php
$c->config['my param']
```
Registered with something like this:
```php
'config' => function($c) { return $c->config = require __DIR__ . '/config.php'; }
```
Note how `$c->config` is replaced, so the array will be returned directly the next time!


Get a database connection:
```php
$db = $c->db;
```
Note that even though we are getting the `$c->db` property, internally a Closure has been registered something like this:
```php
'db' => function($c) { return $c->db = new Db($c); }
```
When `$c->db` is called the next time, the same (singleton) instance is returned.


Get the request object
```php
$request = $c->request;
```
The code above actually calls a function registered like this:
```php
'request' => function($c) { return $c->request = new Request($c); }
```


These 'dynamic properties' have been pre-configured in the 'controller' config file, they use `__get`, `__set` and `__call`.
More properties can easily be added:
```php
$c->session = new Session();
$c->isSecure = $uri->scheme === 'https';
$c->getDummy = function($c) { return $c->dummy; };
$c->setDummy = function($c, $dummy) { $c->dummy = $dummy; return $c; };

$dummy = $c->getDummy();
$c->setDummy($dummy);
```
Note how the container instance is automatically prepended to the parameters of all internal functions (Closures) eg. `function($c)`


## Router

Along with the Container, comes a very flexible and fast router (inside the Request class).
I consider this router to be THE fastest router I've tested, with the same functionality.

It includes the ability to filter by method (GET/POST), and optional parameters like `/user/{id}[/{name}]`.
The code was partially inspired by FastRoute.

Another somewhat unique capability is the ability to pre-define the patterns associated with named parameters eg. `id`=>`\d+`
So everytime you specify `{id}`, `{date}`, `{uuid}` etc. in the routes, the pre-configured patterns are used,
or you can specify custom patterns with `{id:[0-9]+}` or `{id:uuid}` where `uuid`=>`[A-F0-9-]+` etc.

Two design choices make the router fast:
* Everything is configured/loaded from a `config` array file (which is usually cached by APC/Xcode/PHP7)
* The router splits the request uri by '/', doing an `isset` array lookup for the first path segment
* The router only takes further action after the first segment (eg. /admin/, /login etc.) is resolved. ie. The routes are grouped by 'prefix', so all /admin/\* routes are grouped together.

## Philosophy

My main design philosophy for the router and Container is: Configuration over Code/Convention; where 'configuration' isn't something you do once and leave, but it becomes part of the overall/ongoing development process. As you build the Container, you extend/enhance its capabilities/functionality by added more configurable components. The same applies to the router, its single configuration file determines all the routes, parameters, callbacks etc. All together!

I would rather write a large array with hundreds of pre-configured properties/routes/Closures/functions etc.,
and have the benefit of a pre-cached array (PHP7 includes a built in cache, or XCode/APC),
than have the overhead of hundreds of (unecessary) `->add(...)` function calls. ie. A single array in a config file with 300 pre-configured lines of routes, is faster (and easier to manage) than 300 `->add()` route function calls, due to the function call overhead, which can become significant the larger a project gets, and also because the array is much less verbose!
I just see very little benefit to writing hundreds of `->add(route)` commands when the entire route layout of your website can be loaded once, and visible/configurable in a single location.

One argument for writing `->add(...)` calls in the Container and Router is input validation,
but I would argue that you can still do it by validating the single pre-configured array. One large pre-configured array with default Routes and Container objects could serve as the 'basis' for default options. Additional Routes/DI/IoC objects could be added/modified at run-time. Also, I ONLY use PHP array based configuration files, because they are cached natively by PHP; any other configuration files (.ini/YAML/JSON) have to be interpreted/parsed at runtime or a custom cache has to be invented.

There is actually another reason I prefer this method, and that is because when all the routes are visible/grouped together, it's MUCH easier to see the overall picture, and make changes to them rapidly. This method/technique has proven to be MUCH more productive in the long run (compared to my experiences with Symfony and Laravel) as the project gets bigger, making it easier to find and correct errors (in larger projects) and much faster to add new routes/IoC/DI objects. I've worked on large projects with hundreds of routes before, especially with annotations, but even with function calls to add routes, it becomes a nightmare to manage. Try adding 300 different routes in 100 controllers and see how much fun you are NOT having! I swear I must have reduced my life expectancy from the nightmares.

So my philosophy is simple; just KISS!

## Proof-of-concept

These components form the core of my framework, but it's more a proof-of-concept for me to demonstrate and experiment on a few concepts and alternative design decisions.

After all, if we never re-invented the wheel, we would still be driving horse-drawn carriages; or worse!

## Benchmarks:

All tests were done with a skeleton `hello world` application on the same PC.
Laravel and Symfony were NOT configured to establish a database connection, while Twister WAS!
With a database connection, Symfony dropped to 9-12 requests per second, and Laravel 12-16 rps,
Twister is running anywhere from 50x-100x faster than Symfony and Laravel, and uses much less memory!
  
```
ab -t 30 http://laravel/

Complete requests:      198
Requests per second:    6.59 [#/sec] (mean)
Time per request:       151.765 [ms] (mean)

memory_get_usage(): 6,621,416
memory_get_usage(true): 2,097,152
memory_get_peak_usage(): 6,691,272
memory_get_peak_usage(true): 2,097,152

ab -t 30 http://symfony/

Complete requests:      95
Requests per second:    3.15 [#/sec] (mean)
Time per request:       317.717 [ms] (mean)

memory_get_usage(): 10,887,352
memory_get_usage(true): 4,194,304
memory_get_peak_usage(): 11,168,984
memory_get_peak_usage(true): 4,194,304

ab -t 30 http://twister/

Complete requests:      911
Requests per second:    30.35 [#/sec] (mean)
Time per request:       32.952 [ms] (mean)

memory_get_usage(): 858,848
memory_get_usage(true): 2,097,152
memory_get_peak_usage(): 1,049,528
memory_get_peak_usage(true): 2,097,152
```
