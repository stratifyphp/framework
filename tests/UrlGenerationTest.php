<?php

namespace Stratify\Framework\Test;

require_once __DIR__ . '/../.puli/GeneratedPuliFactory.php';

use Psr\Http\Message\ServerRequestInterface;
use Puli\Repository\Api\ResourceRepository;
use Puli\Repository\FilesystemRepository;
use Stratify\Framework\Application;
use Stratify\Framework\Test\Mock\FakeResponseEmitter;
use Stratify\Framework\UrlGenerator;
use Stratify\Http\Response\SimpleResponse;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\ServerRequest;
use function Stratify\Router\route;
use function Stratify\Framework\router;

class UrlGenerationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FakeResponseEmitter
     */
    private $responseEmitter;

    public function setUp()
    {
        $this->responseEmitter = new FakeResponseEmitter;
    }

    /**
     * @test
     */
    public function generates_route_with_root_router()
    {
        $http = router([
            '/' => route(function (UrlGenerator $urlGenerator) {
                $content = $urlGenerator->routeUrl('about');
                return new SimpleResponse($content);
            }),
            '/about' => route(function () {
                return new SimpleResponse('');
            }, 'about'),
        ]);

        $this->runHttp($http, new ServerRequest([], [], '/', 'GET'));
        $this->assertEquals('/about', $this->responseEmitter->output);
    }

    private function runHttp($http, ServerRequestInterface $request = null) : Application
    {
        $app = new Application([], 'prod', $http);
        $app->addConfig([
            EmitterInterface::class => $this->responseEmitter,
            // Override the ResourceRepository
            ResourceRepository::class => new FilesystemRepository(__DIR__),
        ]);
        $app->http()->run($request);
        return $app;
    }
}
