<?php

use DI\Container;
use Interop\Container\ContainerInterface;
use Invoker\Invoker;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\ResolverChain;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response\SapiEmitter;
use function DI\get;
use function DI\object;

return [

    ContainerInterface::class => get(Container::class),

    EmitterInterface::class => get(SapiEmitter::class),

    'router.invoker' => function (ContainerInterface $c) {
        $resolvers = [
            new AssociativeArrayResolver,
            new TypeHintContainerResolver($c),
        ];
        return new Invoker(new ResolverChain($resolvers), $c);
    },

];
