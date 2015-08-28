<?php

namespace Stratify\Framework;

use DI\ContainerBuilder;
use Interop\Container\ContainerInterface;
use Puli\Repository\Api\ResourceRepository;
use Puli\UrlGenerator\Api\UrlGenerator;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ContainerFactory
{
    /**
     * @param string|array $config
     */
    public static function create($config = []) : ContainerInterface
    {
        $builder = new ContainerBuilder;

        $factoryClass = PULI_FACTORY_CLASS;
        $puli = new $factoryClass();

        /** @var ResourceRepository $resourceRepository */
        $resourceRepository = $puli->createRepository();
        $resourceDiscovery = $puli->createDiscovery($resourceRepository);
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
}
