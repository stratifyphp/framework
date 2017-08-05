<?php

namespace Stratify\Framework\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Invoker\Invoker;
use Invoker\ParameterResolver\NumericArrayResolver;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stratify\Http\Middleware\Invoker\MiddlewareInvoker;

/**
 * Resolves middleware from a container.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ContainerBasedInvoker implements MiddlewareInvoker
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
        DelegateInterface $delegate
    ) : ResponseInterface {
        if (! $this->invoker) {
            $this->invoker = new Invoker(new NumericArrayResolver, $this->container);
        }

        if ($middleware instanceof MiddlewareInterface) {
            $callable = [$middleware, 'process'];
        } else {
            $callable = $middleware;
        }

        return $this->invoker->call($callable, [$request, $delegate]);
    }
}
