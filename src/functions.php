<?php

namespace Stratify\Framework;

use Psr\Container\ContainerInterface;
use Stratify\Framework\Middleware\MiddlewareFactory;
use Stratify\Http\Middleware\Middleware;
use Stratify\Http\Middleware\Pipe;
use Stratify\Router\PrefixRouter;
use Stratify\Router\Router;

if (! function_exists('Stratify\Framework\pipe')) {

    /**
     * Create a pipe middleware.
     *
     * @param array $middlewares Middlewares to execute in order.
     */
    function pipe(array $middlewares) : MiddlewareFactory
    {
        return new class($middlewares) implements MiddlewareFactory {
            private $middlewares;
            public function __construct(array $middlewares)
            {
                $this->middlewares = $middlewares;
            }
            public function create(ContainerInterface $container, array $newSubMiddlewares) : Middleware
            {
                return new Pipe($newSubMiddlewares, $container->get('middleware_invoker'));
            }
            public function getSubMiddlewares() : array
            {
                return $this->middlewares;
            }
        };
    }

    /**
     * Create a router middleware.
     *
     * @param array|string $routes Array of routes or path to a file returning the route array.
     */
    function router($routes) : MiddlewareFactory
    {
        return new class($routes) implements MiddlewareFactory {
            private $routes;
            public function __construct($routes)
            {
                $this->routes = $routes;
            }
            public function create(ContainerInterface $container, array $newSubMiddlewares) : Middleware
            {
                return new Router($newSubMiddlewares, $container);
            }
            public function getSubMiddlewares()
            {
                return $this->routes;
            }
        };
    }

    /**
     * Create a PrefixRouter middleware.
     *
     * It routes to sub-middleware based on what the URL starts with.
     *
     * @param array $routes Mapping of URL prefixes to the middleware to execute.
     */
    function prefix(array $routes) : MiddlewareFactory
    {
        return new class($routes) implements MiddlewareFactory {
            private $routes;
            public function __construct(array $routes)
            {
                $this->routes = $routes;
            }
            public function create(ContainerInterface $container, array $newSubMiddlewares) : Middleware
            {
                return new PrefixRouter($newSubMiddlewares, $container->get('middleware_invoker'));
            }
            public function getSubMiddlewares() : array
            {
                return $this->routes;
            }
        };
    }

}
