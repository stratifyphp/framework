<?php

namespace Stratify\Framework\Config;

use Interop\Container\ContainerInterface;
use Puli\Repository\Api\ResourceRepository;
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
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var MiddlewareInvoker
     */
    private $middlewareInvoker;

    /**
     * @var ResourceRepository
     */
    private $resourceRepository;

    public function __construct(
        ContainerInterface $container,
        MiddlewareInvoker $middlewareInvoker,
        ResourceRepository $resourceRepository
    ) {
        $this->middlewareInvoker = $middlewareInvoker;
        $this->resourceRepository = $resourceRepository;
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

        $subNodes = $node->getSubNodes();
        if (is_string($subNodes)) {
            // Load from a file
            $file = $this->resourceRepository->get($subNodes)->getFilesystemPath();
            $subNodes = require $file;

            if (!is_array($subNodes)) {
                throw new \Exception(sprintf('The file %s must return an array', $file));
            }
        }

        // Recursive compilation in the array
        $subNodes = array_map([$this, 'compile'], $subNodes);

        switch ($node->getName()) {
            case 'pipe':
                return new MiddlewarePipe($subNodes, $this->middlewareInvoker);
            case 'router':
                return new Router($subNodes, $this->container);
            case 'prefix':
                return new PrefixRouter($subNodes, $this->middlewareInvoker);
            default:
                throw new \Exception(sprintf('Unknown node of type %s', $node->getName()));
        }
    }
}
