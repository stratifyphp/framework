<?php

namespace Stratify\Framework;

use Stratify\Http\Middleware\MiddlewareStack;
use Stratify\Router\Router;

function stack(array $middlewares)
{
    return new MiddlewareStack($middlewares);
}

function router(array $routes)
{
    return Router::fromArray($routes);
}
