<?php

use DI\Container;
use Interop\Container\ContainerInterface;
use Stratify\Framework\Middleware\ContainerBasedInvoker;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response\SapiEmitter;
use function DI\get;
use function DI\object;

return [

    \Stratify\Http\Application::class => object()
        ->constructor(get('http'), get('middleware_invoker'), get(EmitterInterface::class)),
    \Silly\Application::class => object()
        ->method('useContainer', get(Container::class), true, true),

    'http' => function () {
        throw new Exception('No HTTP stack was defined');
    },

    ContainerInterface::class => get(Container::class),
    EmitterInterface::class => get(SapiEmitter::class),
    'middleware_invoker' => get(ContainerBasedInvoker::class),

];
