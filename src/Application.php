<?php

namespace Stratify\Framework;

use DI\Kernel\Kernel;
use Interop\Container\ContainerInterface;
use Silly\Application as CliApplication;
use Stratify\Framework\Config\ConfigCompiler;
use Stratify\Framework\Config\Node;
use Stratify\Http\Application as HttpApplication;
use Zend\Diactoros\Response\EmitterInterface;

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
     * @var HttpApplication|null
     */
    private $http;

    /**
     * @var CliApplication|null
     */
    private $cli;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $modules = [], $environment = 'prod')
    {
        array_unshift($modules, 'stratify');

        parent::__construct($modules, $environment);
    }

    /**
     * @param callable|Node $stack
     */
    public function http($stack) : HttpApplication
    {
        if (!$this->http) {
            $container = $this->getContainer();
            /** @var ConfigCompiler $configCompiler */
            $configCompiler = $container->get(ConfigCompiler::class);

            $this->http = new HttpApplication(
                $configCompiler->compile($stack),
                $container->get('middleware_invoker'),
                $container->get(EmitterInterface::class)
            );
        }

        return $this->http;
    }

    public function cli() : CliApplication
    {
        if (!$this->cli) {
            $this->cli = new CliApplication();
            $this->cli->useContainer($this->getContainer(), true, true);
        }

        return $this->cli;
    }

    public function getContainer() : ContainerInterface
    {
        if (!$this->container) {
            $this->container = $this->createContainer();
        }
        return $this->container;
    }
}
