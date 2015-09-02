<?php

namespace Stratify\Framework;

use Stratify\Framework\Config\Node;

function pipe(array $middlewares)
{
    return new Node('pipe', $middlewares);
}

function router(array $routes)
{
    return new Node('router', $routes);
}
