<?php

namespace Stratify\Framework;

use DI\ContainerBuilder;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Puli\Repository\Api\ResourceRepository;
use Puli\UrlGenerator\Api\UrlGenerator;
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
        if (!empty($config)) {
            $modules[] = $config;
        }

        $this->container = $this->createContainer($modules);

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

    /**
     * Override this method to customize how the container is created.
     *
     * @param array $modules Array of definition files/arrays
     */
    protected function createContainer(array $modules) : ContainerInterface
    {
        $containerBuilder = $this->createContainerBuilder($modules);

        return $containerBuilder->build();
    }

    /**
     * Override this method to configure the container builder.
     *
     * @param array $modules Array of definition files/arrays
     */
    protected function createContainerBuilder(array $modules) : ContainerBuilder
    {
        $builder = new ContainerBuilder;

        $factoryClass = PULI_FACTORY_CLASS;
        $puli = new $factoryClass();

        /** @var ResourceRepository $resourceRepository */
        $resourceRepository = $puli->createRepository();
        $resourceDiscovery = $puli->createDiscovery($resourceRepository);

        $builder->addDefinitions([
            'puli.factory' => $puli,
            ResourceRepository::class => $resourceRepository,
            UrlGenerator::class => $puli->createUrlGenerator($resourceDiscovery),
        ]);

        $this->addModule($builder, $resourceRepository, 'stratify');

        foreach ($modules as $module) {
            $this->addModule($builder, $resourceRepository, $module);
        }

        return $builder;
    }

    private function addModule(ContainerBuilder $builder, ResourceRepository $resources, $module)
    {
        if (is_string($module)) {
            // Module name
            $file = '/' . $module . '/config/config.php';
            $builder->addDefinitions($resources->get($file)->getFilesystemPath());
        } else {
            // Definition array
            assert(is_array($module));
            $builder->addDefinitions($module);
        }
    }
}
