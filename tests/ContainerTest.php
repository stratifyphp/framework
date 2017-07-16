<?php

namespace Stratify\Framework\Test;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Stratify\Framework\Application;
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
}
