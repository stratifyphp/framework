<?php

namespace Stratify\Framework;

use DI\Kernel\Kernel;
use Psr\Container\ContainerInterface;
use Silly\Application as CliApplication;
use Stratify\Framework\Middleware\MiddlewareFactory;
use Stratify\Framework\Middleware\TreeCompiler;
use Stratify\Http\Application as HttpApplication;
use Stratify\Http\Middleware\Middleware;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Application extends Kernel
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     * @param callable|Middleware|MiddlewareFactory $httpStack
     */
    public function __construct(array $modules = [], string $environment = 'prod', $httpStack = null)
    {
        array_unshift($modules, 'stratify/framework');

        if ($httpStack) {
            $this->addConfig([
                'http' => function (ContainerInterface $container) use ($httpStack) {
                    $treeCompiler = $container->get(TreeCompiler::class);
                    return $treeCompiler->compile($httpStack);
                },
            ]);
        }

        parent::__construct($modules, $environment);
    }

    public function http() : HttpApplication
    {
        return $this->getContainer()->get(HttpApplication::class);
    }

    public function cli() : CliApplication
    {
        return $this->getContainer()->get(CliApplication::class);
    }

    public function getContainer() : ContainerInterface
    {
        if (!$this->container) {
            $this->container = $this->createContainer();
        }
        return $this->container;
    }
}
