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
     * @param array $modules Array of definition files/arrays
     */
    public static function create(array $modules) : ContainerInterface
    {
        $builder = new ContainerBuilder;

        $factoryClass = PULI_FACTORY_CLASS;
        $puli = new $factoryClass();

        /** @var ResourceRepository $resourceRepository */
        $resourceRepository = $puli->createRepository();
        $resourceDiscovery = $puli->createDiscovery($resourceRepository);

        self::addModule($builder, $resourceRepository, 'stratify');

        foreach ($modules as $module) {
            self::addModule($builder, $resourceRepository, $module);
        }

        $builder->addDefinitions([
            'puli.factory' => $puli,
            ResourceRepository::class => $resourceRepository,
            UrlGenerator::class => $puli->createUrlGenerator($resourceDiscovery),
        ]);

        return $builder->build();
    }

    private static function addModule(ContainerBuilder $builder, ResourceRepository $resources, $module)
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
