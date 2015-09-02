<?php

namespace Stratify\Framework;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stratify\Framework\Config\ConfigCompiler;
use Stratify\Framework\Config\Node;
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
     * @param callable|Node $http
     * @param array         $modules
     * @param string|array  $config
     */
    public function __construct($http, array $modules = [], $config = [])
    {
        if (! empty($config)) {
            $modules[] = $config;
        }

        $this->container = ContainerFactory::create($modules);

        /** @var ConfigCompiler $configCompiler */
        $configCompiler = $this->container->get(ConfigCompiler::class);
        $this->http = $configCompiler->compile($http);
    }

    public function runHttp(ServerRequestInterface $request = null)
    {
        $invoker = $this->container->get('invoker.middlewares');
        $responseEmitter = $this->container->get(EmitterInterface::class);

        $app = new \Stratify\Http\Application($this->http, $invoker, $responseEmitter);
        $app->run($request);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
