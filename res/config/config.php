<?php

use DI\Container;
use Stratify\Framework\Middleware\ContainerBasedInvoker;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response\SapiEmitter;
use function DI\get;
use function DI\create;

return [

    \Stratify\Http\Application::class => create()
        ->constructor(get('http'), get('middleware_invoker'), get(EmitterInterface::class)),
    \Silly\Application::class => create()
        ->method('useContainer', get(Container::class), true, true),

    'http' => function () {
        throw new Exception('No HTTP stack was defined');
    },

    EmitterInterface::class => get(SapiEmitter::class),
    'middleware_invoker' => get(ContainerBasedInvoker::class),

];
