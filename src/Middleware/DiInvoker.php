<?php

namespace Stratify\Framework\Middleware;

use Interop\Container\ContainerInterface;
use Invoker\Invoker;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\ResolverChain;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stratify\Http\Middleware\Invoker\MiddlewareInvoker;

/**
 * Invokes middlewares with dependency injection features:
 *
 * - resolves them from the container if they aren't callable
 * - passes parameters based on the parameter names
 * - do dependency injection in parameters (based on type-hints)
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class DiInvoker implements MiddlewareInvoker
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Invoker|null
     */
    private $invoker;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function invoke(
        $middleware,
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) : ResponseInterface
    {
        if (! $this->invoker) {
            $resolvers = [
                new AssociativeArrayResolver,
                new TypeHintContainerResolver($this->container),
            ];
            $this->invoker = new Invoker(new ResolverChain($resolvers), $this->container);
        }

        $parameters = $request->getAttributes();
        $parameters['request'] = $request;
        $parameters['response'] = $response;
        $parameters['next'] = $next;

        return $this->invoker->call($middleware, $parameters);
    }
}
