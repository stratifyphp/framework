<?php

namespace Stratify\Framework\Test;

use Interop\Container\ContainerInterface;
use Puli\Discovery\Api\Discovery;
use Puli\Repository\Api\ResourceRepository;
use Puli\UrlGenerator\Api\UrlGenerator;
use Stratify\Framework\Application;
use Zend\Diactoros\Request;
use function Stratify\Router\route;
use function Stratify\Framework\pipe;
use function Stratify\Framework\prefix;
use function Stratify\Framework\router;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response\SapiEmitter;

class ContainerTest extends \PHPUnit_Framework_TestCase
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
    public function registers_puli_factory()
    {
        $container = (new Application)->getContainer();
        $this->assertTrue($container->has('puli.factory'));
    }

    /**
     * @test
     */
    public function registers_puli_repository()
    {
        $container = (new Application)->getContainer();
        $this->assertInstanceOf(ResourceRepository::class, $container->get(ResourceRepository::class));
    }

    /**
     * @test
     */
    public function registers_puli_discovery()
    {
        $container = (new Application)->getContainer();
        $this->assertInstanceOf(Discovery::class, $container->get(Discovery::class));
    }

    /**
     * @test
     */
    public function registers_puli_url_generator()
    {
        $container = (new Application)->getContainer();
        $this->assertInstanceOf(UrlGenerator::class, $container->get(UrlGenerator::class));
    }
}
