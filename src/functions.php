<?php

namespace Stratify\Framework;

use Stratify\Framework\Config\Node;

if (! function_exists('Stratify\Framework\pipe')) {

    /**
     * Create a pipe middleware.
     *
     * @param array $middlewares Middlewares to execute in order.
     */
    function pipe(array $middlewares) : Node
    {
        return new Node('pipe', $middlewares);
    }

    /**
     * Create a router middleware.
     *
     * @param array|string $routes Array of routes or Puli path to a file returning the route array.
     */
    function router($routes) : Node
    {
        return new Node('router', $routes);
    }

    /**
     * Create a PrefixRouter middleware.
     *
     * It routes to sub-middleware based on what the URL starts with.
     *
     * @param array $routes Mapping of URL prefixes to the middleware to execute.
     */
    function prefix(array $routes) : Node
    {
        return new Node('prefix', $routes);
    }

}
