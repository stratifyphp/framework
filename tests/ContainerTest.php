<?php

namespace Stratify\Framework\Test;

use DI\CompiledContainer;
use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Stratify\Framework\Application;
use function Stratify\Framework\pipe;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response\SapiEmitter;

class ContainerTest extends TestCase
{
    /**
     * @test
     */
    public function registers_container()
    {
        $container = (new Application)->getContainer();
        $this->assertSame($container, $container->get(ContainerInterface::class));
    }

    /**
     * @test
     */
    public function registers_response_emitter()
    {
        $container = (new Application)->getContainer();
        $this->assertInstanceOf(SapiEmitter::class, $container->get(EmitterInterface::class));
    }

    /**
     * @test
     */
    public function can_be_compiled()
    {
        if (file_exists(__DIR__ . '/tmp/CompiledContainer.php')) {
            unlink(__DIR__ . '/tmp/CompiledContainer.php');
        }

        $app = new class([], 'prod', pipe([])) extends Application {
            protected function configureContainerBuilder(ContainerBuilder $containerBuilder)
            {
                $containerBuilder->enableCompilation(__DIR__ . '/tmp');
            }
        };
        $container = $app->getContainer();

        $this->assertInstanceOf(CompiledContainer::class, $container);
        $this->assertInstanceOf(\Stratify\Http\Application::class, $app->http());
        $this->assertInstanceOf(\Silly\Application::class, $app->cli());
    }
}
