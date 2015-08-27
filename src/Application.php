<?php

namespace Stratify\Framework;

use DI\Container;
use DI\ContainerBuilder;
use Psr\Http\Message\ServerRequestInterface;
use Stratify\Framework\Config\ConfigCompiler;
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
    public function __construct($http, $definitions = null)
    {
        $this->container = $this->createContainer($definitions);

        /** @var ConfigCompiler $configCompiler */
        $configCompiler = $this->container->get(ConfigCompiler::class);
        $this->http = $configCompiler->compile($http);
    }

    public function runHttp(ServerRequestInterface $request = null)
    {
        $responseEmitter = $this->container->get(EmitterInterface::class);

        $app = new \Stratify\Http\Application($this->http, $responseEmitter);
        $app->run($request);
    }

    private function createContainer($definitions = null) : Container
    {
        $builder = new ContainerBuilder;
        $builder->addDefinitions(__DIR__ . '/config.php');
        if ($definitions !== null) {
            $builder->addDefinitions($definitions);
        }

        return $builder->build();
    }
}
