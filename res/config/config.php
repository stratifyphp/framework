<?php

use DI\Container;
use Interop\Container\ContainerInterface;
use Stratify\Framework\Middleware\DiInvoker;
use Stratify\Http\Middleware\Invoker\MiddlewareInvoker;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response\SapiEmitter;
use function DI\get;
use function DI\object;

return [

    ContainerInterface::class => get(Container::class),

    EmitterInterface::class => get(SapiEmitter::class),

    MiddlewareInvoker::class => get(DiInvoker::class),

];
