<?php

namespace Stratify\Framework\Middleware;

use DI\Container;
use Puli\Repository\Api\ResourceRepository;
use Stratify\Http\Middleware\Middleware;
use Stratify\Router\UrlGenerator;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class TreeCompiler
{
    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Compile a middleware tree.
     *
     * @param Middleware|callable|string|MiddlewareFactory $middleware Middleware tree/node.
     */
    public function compile($middleware)
    {
        // Compile lazy middlewares
        if ($middleware instanceof MiddlewareFactory) {
            $subMiddlewares = $middleware->getSubMiddlewares();

            if (is_string($subMiddlewares)) {
                $subMiddlewares = $this->loadFromFile($subMiddlewares);
            }

            // Recursive compilation in sub-middlewares
            $subMiddlewares = array_map([$this, 'compile'], $subMiddlewares);

            $middleware = $middleware->create($this->container, $subMiddlewares);
        }

        // Register URL generators in the container
        if ($middleware instanceof UrlGenerator) {

        }

        return $middleware;
    }

    private function loadFromFile(string $file) : array
    {
        // Not injected to avoid instantiating the object when not used
        $resourceRepository = $this->container->get(ResourceRepository::class);

        $file = $resourceRepository->get($file)->getFilesystemPath();
        $subNodes = require $file;

        if (! is_array($subNodes)) {
            throw new \Exception(sprintf('The file %s must return an array', $file));
        }

        return $subNodes;
    }
}
