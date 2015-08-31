<?php

use DI\Container;
use Interop\Container\ContainerInterface;
use Invoker\Invoker;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\ResolverChain;
use Stratify\Framework\Config\ConfigCompiler;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response\SapiEmitter;
use function DI\get;
use function DI\object;

return [

    ContainerInterface::class => get(Container::class),

    EmitterInterface::class => get(SapiEmitter::class),

    ConfigCompiler::class => object()
        ->constructor(get('middleware.invoker')),

    'middleware.invoker' => function (ContainerInterface $c) {
        $resolvers = [
            new AssociativeArrayResolver,
            new TypeHintContainerResolver($c),
        ];
        return new Invoker(new ResolverChain($resolvers), $c);
    },

];
