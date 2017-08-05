<?php

namespace Stratify\Framework\Middleware;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use Stratify\Http\Middleware\Middleware;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class TreeCompiler
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Compile a middleware tree.
     *
     * @param MiddlewareInterface|callable|MiddlewareFactory $middleware Middleware tree/node.
     *
     * @return MiddlewareInterface|callable
     */
    public function compile($middleware)
    {
        if ($middleware instanceof MiddlewareFactory) {
            $subMiddlewares = $middleware->getSubMiddlewares();

            // Recursive compilation in sub-middlewares
            $subMiddlewares = array_map([$this, 'compile'], $subMiddlewares);

            $middleware = $middleware->create($this->container, $subMiddlewares);
        }

        return $middleware;
    }
}
