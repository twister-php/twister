# twister
> Twister is a fast and light-weight micro-framework component library

Another definition of this code release would be;
> Twister is a set of fast and light-weight components around which a framework can be written

There is also a [skeleton application](https://github.com/twister-php/skeleton) based on this code!

At the heart of the framework, sits a very flexible, simple and elegant Inversion-of-Control (IoC) Container.
In fact, there are NO global variables, NO define's, NO pipeline, NO Kernel and NO App; just the Container.
The Container controls the entire flow of code (except routing), with a custom `execute()` function (written by you);
    which is actually just an anonymous callback function inside the Container, called from the Front Controller (index.php);
    even the name of that function can be changed in the config file.
    ie. There is NO pre-programmed flow of the program or hard-coded Kernel/App.
      Most things are handled/registered with the IoC Container, objects are pre-configured and `lazy-loaded` on request/use only!

Along with the Container, comes a very flexible and fast router (inside the Request class).
    I consider this router to be THE fastest router I've tested, with the same functionality.
    It includes the ability to filter by method (GET/POST), and optional parameters like `/user/{id}[/{name}]`
    Another somewhat unique capability is the ability to pre-define the patterns associated with named parameters eg. `id`=>`\d+`
        So everytime you specify `{id}`, `{date}`, `{uuid}` etc. in the routes, the pre-configured patterns are used,
          or you can specify custom patterns with `{id:[0-9]+}` or `{id:uuid}` where `uuid`=>`[A-F0-9-]+` etc.
    Two design choices make the router fast:
* Everything is configured/loaded from a `config` array (which is usually cached by APC/Xcode/PHP7)
* The router splits the request uri by '/', doing an `isset` array lookup for the first path segment
* The router only takes further action after the first segment (eg. /admin/, /login etc.) is resolved

Although Twister is a fully functional and useable framework (based on my personal framework),
    it's more a proof-of-concept for me to demonstrate my capabilities and design decisions.

My main design philosophy is: Configuration over Code/Convention; where 'configuration' isn't something you do once and leave, but it becomes part of the overall/ongoing development process. As you build the Container, you extend/enhance its capabilities/functionality by added more configurable components.
    I would rather write a large array with hundreds of pre-configured properties/routes/Closures/functions etc.,
        and have the benefit of a pre-cached array (PHP7 includes a built in cache, or XCode/APC),
        than have the overhead of hundreds of (unecessary) `->add(...)` function calls. ie. A single array in a config file with 300 pre-configured lines of routes, is faster than 300 `->add()` route function calls, due to the function call overhead, which can become significant the larger a project gets.
    I just see very little benefit to writing hundreds of `->add(route)` commands when the entire route layout of your website can be loaded once.
    One argument for writing `->add(...)` calls in the Container and Router is input validation,
        but I would argue that you can still do it by parsing a single pre-configured array. One large pre-configured array with default Routes and Container objects could serve as the 'base' for default options. Additional Routes/DI/IoC objects could be added/modified at run-time. Also, I ONLY use PHP array based configuration files, because they are cached natively by PHP; any other configuration files (.ini/YAML/JSON) have to be interpreted/parsed at runtime or a custom cache has to be invented.



## Benchmarks:

All tests were done with a skeleton `hello world` application on the same PC.
Laravel and Symfony were NOT configured to establish a database connection, while Twister WAS!
With a database connection, Symfony dropped to 9-12 requests per second, and Laravel 12-16 rps,
  Twister is running anywhere from 50x-100x faster than Symfony and Laravel
  
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
