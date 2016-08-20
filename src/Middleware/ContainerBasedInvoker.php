<?php

namespace Stratify\Framework\Middleware;

use Interop\Container\ContainerInterface;
use Invoker\Invoker;
use Invoker\ParameterResolver\NumericArrayResolver;
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

    public function invoke($middleware, ServerRequestInterface $request, callable $next) : ResponseInterface
    {
        if (! $this->invoker) {
            $this->invoker = new Invoker(new NumericArrayResolver, $this->container);
        }

        return $this->invoker->call($middleware, [$request, $next]);
    }
}
