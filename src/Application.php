<?php

namespace Stratify\Framework;

use DI\Container;
use DI\ContainerBuilder;
use Psr\Http\Message\ServerRequestInterface;
use Puli\Discovery\Api\ResourceDiscovery;
use Puli\Repository\Api\ResourceRepository;
use Puli\UrlGenerator\Api\UrlGenerator;
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
     * @param callable     $http
     * @param string|array $config
     */
    public function __construct($http, $config = [])
    {
        $this->container = $this->createContainer($config);

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

    private function createContainer($config = []) : Container
    {
        $builder = new ContainerBuilder;

        $puli = $this->createPuliFactory();
        /** @var ResourceRepository $resourceRepository */
        $resourceRepository = $puli->createRepository();
        /** @var ResourceDiscovery $resourceDiscovery */
        $resourceDiscovery = $puli->createDiscovery($resourceRepository);
        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $puli->createUrlGenerator($resourceDiscovery);

        $builder->addDefinitions($resourceRepository->get('/stratify/config.php')->getFilesystemPath());

        if (is_string($config)) {
            $builder->addDefinitions($resourceRepository->get($config)->getFilesystemPath());
            $config = [];
        }

        $config['puli.factory'] = $puli;
        $config[ResourceRepository::class] = $resourceRepository;
        $config[UrlGenerator::class] = $urlGenerator;
        $builder->addDefinitions($config);

        return $builder->build();
    }

    private function createPuliFactory()
    {
        $factoryClass = PULI_FACTORY_CLASS;
        return (new $factoryClass());
    }
}
