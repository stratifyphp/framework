<?php

use DI\Container;
use Interop\Container\ContainerInterface;
use Stratify\Framework\Config\ConfigCompiler;
use Stratify\Framework\Middleware\ContainerBasedInvoker;
use Stratify\Framework\Middleware\ControllerInvoker;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response\SapiEmitter;
use function DI\get;
use function DI\object;

return [

    ContainerInterface::class => get(Container::class),

    EmitterInterface::class => get(SapiEmitter::class),

    ConfigCompiler::class => object()
        ->constructor(get('invoker.middlewares'), get('invoker.controllers')),
    'invoker.middlewares' => get(ContainerBasedInvoker::class),
    'invoker.controllers' => get(ControllerInvoker::class),

];
