Stratify is a try at writing a middleware-based web framework.

*Work in progress*

A quick intro:

- PHP 7.1 and up
- middleware-oriented HTTP stack
- [http-interop](https://github.com/http-interop/http-middleware) middlewares
- dependency injection and config with [PHP-DI](http://php-di.org/)
- module system based on the [PHP-DI kernel](https://github.com/PHP-DI/Kernel)
- CLI console with [Silly](https://github.com/mnapoli/silly)

The documentation is not up to date, and the project is highly likely to change, TODO:

- remove magic and architecture
- implement PSR-15

## Getting started

```
composer require stratify/framework
```

Example of `web/index.php`:

```php
<?php

use Stratify\ErrorHandlerModule\ErrorHandlerMiddleware;
use Stratify\Framework\Application;
use Zend\Diactoros\Response\HtmlResponse;
use function Stratify\Framework\pipe;
use function Stratify\Framework\router;

require __DIR__ . '/vendor/autoload.php';

// Prepare the HTTP stack
$http = pipe([
    // This is a list of middlewares
    
    // The first middleware is the error handler, it will
    // catch all errors and display the error page
    ErrorHandlerMiddleware::class,
    
    // The application's router
    // See https://github.com/stratifyphp/router for more details
    router([
        // Routes
        '/' => function () {
            return new HtmlResponse('Welcome!');
        },
        '/about' => function () {
            return new HtmlResponse('More information about us');
        },
    ]),
]);

// List of packages containing PHP-DI config to include
// See https://github.com/PHP-DI/Kernel for more details
$modules = [
    'stratify/error-handler-module', // error handling
];

// The application environment
$env = 'dev';

$app = new Application($modules, $env, $http);

// Run the HTTP application
$app->http()->run();
```

### Adding PHP-DI configuration

PHP-DI configuration is managed by the [PHP-DI Kernel](https://github.com/PHP-DI/Kernel), read [its documentation](https://github.com/PHP-DI/Kernel) to learn more. Below is a short example.

Set up your application name in `composer.json`, this will be used by PHP-DI's kernel as your *module* name:

```json
{
    "name": "app",
    ...
}
```

You can use any name you want, `app` is a good default (like Symfony's *AppBundle*).

Add your module name to the list of PHP-DI modules to load:

```php
$modules = [
    'stratify/error-handler-module',
    'app',
];
```

### Twig

Install the Twig module:

```
composer require stratify/twig-module
```

Enable the package module in the PHP-DI module list:

```
$modules = [
    'stratify/error-handler-module',
    'stratify/twig-module',
    ...
];
```

You can configure the directory in which views are stored using PHP-DI config (see the section above):

```php
return [
    ...

    'twig.paths' => [
        // Configure the directory using the alias `app`
        // In this example views are stored in the `res/views/` directory
        // You can then use the `@app/...` notation to render or include Twig templates
        'app' => __DIR__ . '/../views',
    ],
];
```

You can then inject the `Twig_Environment` class in your services, or in your controllers. For example:

```php
$http = pipe([
    ErrorHandlerMiddleware::class,
    router([
        // Routes
        '/' => function (Twig_Environment $twig) {
            return $twig->render('@app/home.twig');
        },
    ]),
]);
```
