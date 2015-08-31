<?php

namespace Stratify\Framework\Config;

use Stratify\Http\Middleware\Invoker\MiddlewareInvoker;
use Stratify\Http\Middleware\MiddlewareStack;
use Stratify\Router\Router;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ConfigCompiler
{
    /**
     * @var MiddlewareInvoker
     */
    private $invoker;

    public function __construct(MiddlewareInvoker $invoker)
    {
        $this->invoker = $invoker;
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
            case 'stack':
                return new MiddlewareStack($subNodes, $this->invoker);
            case 'router':
                return new Router($subNodes, $this->invoker);
            default:
                throw new \Exception(sprintf('Unknown node of type %s', $node->getName()));
        }
    }
}
