<?php

namespace Stratify\Framework;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stratify\Framework\Config\ConfigCompiler;
use Zend\Diactoros\Response\EmitterInterface;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Application
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var callable
     */
    private $http;

    /**
     * @param callable     $http
     * @param string|array $config
     */
    public function __construct($http, $config = [])
    {
        $this->container = ContainerFactory::create($config);

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
}
