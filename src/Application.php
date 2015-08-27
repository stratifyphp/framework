<?php

namespace Stratify\Framework;

use DI\Container;
use DI\ContainerBuilder;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\EmitterInterface;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Application
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var callable
     */
    private $http;

    /**
     * @param string|array $definitions
     * @param callable     $http
     */
    public function __construct($definitions, $http)
    {
        $this->container = $this->createContainer($definitions);
        $this->http = $http;
    }

    public function runHttp(ServerRequestInterface $request = null)
    {
        $responseEmitter = $this->container->get(EmitterInterface::class);

        $app = new \Stratify\Http\Application($this->http, $responseEmitter);
        $app->run($request);
    }

    private function createContainer($definitions) : Container
    {
        $builder = new ContainerBuilder;
        $builder->addDefinitions(__DIR__ . '/config.php');
        $builder->addDefinitions($definitions);

        return $builder->build();
    }
}
