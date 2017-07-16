<?php

use DI\Container;
use function DI\factory;
use Stratify\Framework\Middleware\ContainerBasedInvoker;
use Stratify\Framework\Middleware\TreeCompiler;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response\SapiEmitter;
use function DI\get;
use function DI\create;

return [

    \Stratify\Http\Application::class => create()
        ->constructor(get('http'), get('middleware_invoker'), get(EmitterInterface::class)),
    \Silly\Application::class => create()
        ->method('useContainer', get(Container::class), true, true),

    'http' => factory([TreeCompiler::class, 'compile'])
        ->parameter('middleware', get('http.raw_stack')),
    'http.raw_stack' => function () {
        throw new Exception('No HTTP stack was defined');
    },

    EmitterInterface::class => get(SapiEmitter::class),
    'middleware_invoker' => get(ContainerBasedInvoker::class),

];
