<?php

namespace Stratify\Framework;

use Stratify\Framework\Config\Node;

function stack(array $middlewares)
{
    return new Node('stack', $middlewares);
}

function router(array $routes)
{
    return new Node('router', $routes);
}
