<?php

namespace Stratify\Framework;

use DI\Container;
use DI\ContainerBuilder;
use Stratify\Http\Application;
use Zend\Diactoros\Response\EmitterInterface;

/**
 * Kernel of the application.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Kernel
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param string|array $definitions
     */
    public function __construct($definitions)
    {
        $this->container = $this->createContainer($definitions);
    }

    public function runHttp()
    {
        $middleware = $this->container->get('http.middleware');
        $responseEmitter = $this->container->get(EmitterInterface::class);

        $app = new Application($middleware, $responseEmitter);
        $app->run();
    }

    private function createContainer($definitions) : Container
    {
        $containerBuilder = new ContainerBuilder;
        $containerBuilder->addDefinitions($definitions);

        return $containerBuilder->build();
    }
}
