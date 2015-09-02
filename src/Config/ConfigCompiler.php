<?php

namespace Stratify\Framework\Config;

use Stratify\Http\Middleware\Invoker\MiddlewareInvoker;
use Stratify\Http\Middleware\MiddlewarePipe;
use Stratify\Router\PrefixRouter;
use Stratify\Router\Router;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ConfigCompiler
{
    /**
     * @var MiddlewareInvoker
     */
    private $middlewareInvoker;

    /**
     * @var MiddlewareInvoker
     */
    private $controllerInvoker;

    public function __construct(MiddlewareInvoker $middlewareInvoker, MiddlewareInvoker $controllerInvoker)
    {
        $this->middlewareInvoker = $middlewareInvoker;
        $this->controllerInvoker = $controllerInvoker;
    }

    /**
     * Compile a config into a valid middleware tree.
     */
    public function compile($node)
    {
        if (! $node instanceof Node) {
            return $node;
        }

        $subNodes = array_map([$this, 'compile'], $node->getSubNodes());

        switch ($node->getName()) {
            case 'pipe':
                return new MiddlewarePipe($subNodes, $this->middlewareInvoker);
            case 'router':
                return new Router($subNodes, $this->controllerInvoker);
            case 'prefix':
                return new PrefixRouter($subNodes, $this->middlewareInvoker);
            default:
                throw new \Exception(sprintf('Unknown node of type %s', $node->getName()));
        }
    }
}
