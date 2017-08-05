<?php

namespace Stratify\Framework;

use DI\Kernel\Kernel;
use function DI\value;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use Silly\Application as CliApplication;
use Stratify\Framework\Middleware\MiddlewareFactory;
use Stratify\Http\Application as HttpApplication;

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
     * @param callable|MiddlewareInterface|MiddlewareFactory $httpStack
     */
    public function __construct(array $modules = [], string $environment = 'prod', $httpStack = null)
    {
        array_unshift($modules, 'stratify/framework');

        if ($httpStack) {
            $this->addConfig([
                'http.raw_stack' => value($httpStack),
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
