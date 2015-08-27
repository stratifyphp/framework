<?php

namespace Stratify\Framework\Config;

use Interop\Container\ContainerInterface;
use Stratify\Http\Middleware\MiddlewareStack;
use Stratify\Router\Route\RouteArray;
use Stratify\Router\Router;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ConfigCompiler
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
                return new MiddlewareStack($subNodes);
            case 'router':
                return $this->createRouter($subNodes);
            default:
                throw new \Exception(sprintf('Unknown node of type %s', $node->getName()));
        }
    }

    private function createRouter($routes)
    {
        $invoker = $this->container->get('router.invoker');

        return new Router(new RouteArray($routes), $invoker);
    }
}
