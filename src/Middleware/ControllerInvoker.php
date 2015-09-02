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
 * Invokes controllers with dependency injection features:
 *
 * - resolves them from the container if they aren't callable
 * - passes parameters based on the parameter names
 * - do dependency injection in parameters (based on type-hints)
 *
 * Additionally it allows controllers to return string response (which
 * will be written to the response).
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ControllerInvoker implements MiddlewareInvoker
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

        $newResponse = $this->invoker->call($middleware, $parameters);

        if (is_string($newResponse)) {
            // Allow direct string response
            $response->getBody()->write($newResponse);
            $newResponse = $response;
        } elseif (! $newResponse instanceof ResponseInterface) {
            throw new \RuntimeException(sprintf(
                'The controller did not return a response (expected %s, got %s)',
                ResponseInterface::class,
                is_object($newResponse) ? get_class($newResponse) : gettype($newResponse)
            ));
        }

        return $newResponse;
    }
}
