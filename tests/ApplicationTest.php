<?php

namespace Stratify\Framework\Test;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stratify\Framework\Application;
use function Stratify\Framework\router;
use Stratify\Framework\Test\Mock\FakeResponseEmitter;
use function Stratify\Router\route;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response\EmitterInterface;
use function Stratify\Framework\stack;
use Zend\Diactoros\ServerRequest;

class ApplicationTest extends \PHPUnit_Framework_TestCase
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
    public function calls_middleware()
    {
        $http = function (RequestInterface $request, ResponseInterface $response) {
            $response->getBody()->write('Hello world!');
            return $response;
        };
        $this->runHttp($http);
        $this->assertEquals('Hello world!', $this->responseEmitter->output);
    }

    /**
     * @test
     */
    public function calls_middleware_stack()
    {
        $http = stack([
            function (RequestInterface $request, ResponseInterface $response, callable $next) {
                $response->getBody()->write('Hello');
                return $next($request, $response);
            },
            function (RequestInterface $request, ResponseInterface $response) {
                $response->getBody()->write(' world!');
                return $response;
            },
            function () {
                throw new \Exception;
            },
        ]);
        $this->runHttp($http);
        $this->assertEquals('Hello world!', $this->responseEmitter->output);
    }

    /**
     * @test
     */
    public function calls_router()
    {
        $http = router([
            '/' => function (RequestInterface $request, ResponseInterface $response) {
                $response->getBody()->write('Home');
                return $response;
            },
            '/about' => route(function (RequestInterface $request, ResponseInterface $response) {
                $response->getBody()->write('About');
                return $response;
            }),
        ]);

        $app = $this->runHttp($http, new ServerRequest([], [], '/', 'GET'));
        $this->assertEquals('Home', $this->responseEmitter->output);

        $app->runHttp(new ServerRequest([], [], '/about', 'GET'));
        $this->assertEquals('About', $this->responseEmitter->output);
    }

    /**
     * @test
     */
    public function supports_nesting_middlewares()
    {
        $http = stack([
            router([
                '/' => function (RequestInterface $request, ResponseInterface $response) {
                    $response->getBody()->write('Home');
                    return $response;
                },
                '/api/{resource}' => stack([
                    function (RequestInterface $request, ResponseInterface $response, callable $next) {
                        $response->getBody()->write("Auth check\n");
                        return $next($request, $response);
                    },
                    router([
                        '/api/hello' => function (RequestInterface $request, ResponseInterface $response) {
                            $response->getBody()->write('Hello');
                            return $response;
                        },
                    ])
                ]),
            ]),
        ]);

        $this->runHttp($http, new ServerRequest([], [], '/', 'GET'));
        $this->assertEquals('Home', $this->responseEmitter->output);

        $this->runHttp($http, new ServerRequest([], [], '/api/hello', 'GET'));
        $this->assertEquals("Auth check\nHello", $this->responseEmitter->output);
    }

    /**
     * @test
     */
    public function injects_route_parameters_in_controllers()
    {
        $http = router([
            '/{name}' => function ($name, ResponseInterface $response) {
                $response->getBody()->write('Hello ' . $name);
                return $response;
            },
        ]);

        $this->runHttp($http, new ServerRequest([], [], '/john', 'GET'));
        $this->assertEquals('Hello john', $this->responseEmitter->output);
    }

    private function runHttp($http, ServerRequestInterface $request = null)
    {
        $definitions = [
            EmitterInterface::class => $this->responseEmitter,
        ];
        $app = new Application($definitions, $http);
        $app->runHttp($request);
        return $app;
    }
}
