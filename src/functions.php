<?php

namespace Stratify\Framework;

use Stratify\Framework\Config\Node;

if (! function_exists('Stratify\Framework\pipe')) {

    function pipe(array $middlewares)
    {
        return new Node('pipe', $middlewares);
    }

    function router(array $routes)
    {
        return new Node('router', $routes);
    }

    function prefix(array $routes)
    {
        return new Node('prefix', $routes);
    }

}
