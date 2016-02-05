<?php

use DI\Container;
use Interop\Container\ContainerInterface;
use Stratify\Framework\Config\ConfigCompiler;
use Stratify\Framework\Middleware\ContainerBasedInvoker;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response\SapiEmitter;
use function DI\get;
use function DI\object;

return [

    ContainerInterface::class => get(Container::class),

    EmitterInterface::class => get(SapiEmitter::class),

    ConfigCompiler::class => object()
        ->constructorParameter('middlewareInvoker', get('middleware_invoker')),

    'middleware_invoker' => get(ContainerBasedInvoker::class),

];
