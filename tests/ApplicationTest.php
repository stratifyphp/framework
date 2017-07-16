<?php

namespace Stratify\Framework\Test;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stratify\Framework\Application;
use Stratify\Framework\Test\Mock\FakeResponseEmitter;
use Stratify\Http\Response\SimpleResponse;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\ServerRequest;
use function Stratify\Router\route;
use function Stratify\Framework\pipe;
use function Stratify\Framework\prefix;
use function Stratify\Framework\router;

class ApplicationTest extends TestCase
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
        $http = function (RequestInterface $request, callable $next) {
            return new SimpleResponse('Hello world!');
        };
        $this->runHttp($http);
        $this->assertEquals('Hello world!', $this->responseEmitter->output);
    }

    /**
     * @test
     */
    public function calls_middleware_pipe()
    {
        $http = pipe([
            function (RequestInterface $request, callable $next) {
                $response = $next($request);
                $response->getBody()->write(' world!');
                return $response;
            },
            function () {
                return new SimpleResponse('Hello');
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
            '/' => function () {
                return new SimpleResponse('Home');
            },
            '/about' => route(function () {
                return new SimpleResponse('About');
            }),
        ]);

        $app = $this->runHttp($http, new ServerRequest([], [], '/', 'GET'));
        $this->assertEquals('Home', $this->responseEmitter->output);

        $app->http()->run(new ServerRequest([], [], '/about', 'GET'));
        $this->assertEquals('About', $this->responseEmitter->output);
    }

    /**
     * @test
     */
    public function calls_prefix_router()
    {
        $http = prefix([
            '/admin' => function () {
                return new SimpleResponse('Admin');
            },
            '/api' => router([
                '/api/hello' => function () {
                    return new SimpleResponse('Hello');
                },
                '/api/world' => function () {
                    return new SimpleResponse('World');
                },
            ]),
        ]);

        $app = $this->runHttp($http, new ServerRequest([], [], '/admin/hello', 'GET'));
        $this->assertEquals('Admin', $this->responseEmitter->output);

        $app->http()->run(new ServerRequest([], [], '/api/hello', 'GET'));
        $this->assertEquals('Hello', $this->responseEmitter->output);

        $app->http()->run(new ServerRequest([], [], '/api/world', 'GET'));
        $this->assertEquals('World', $this->responseEmitter->output);
    }

    /**
     * @test
     */
    public function supports_nesting_middlewares()
    {
        $http = pipe([
            router([
                '/' => function () {
                    return new SimpleResponse('Home');
                },
                '/api/{resource}' => pipe([
                    function (RequestInterface $request, callable $next) {
                        $response = $next($request);
                        $response->getBody()->write("\nAuth check");
                        return $response;
                    },
                    router([
                        '/api/hello' => function () {
                            return new SimpleResponse('Hello');
                        },
                    ])
                ]),
            ]),
        ]);

        $this->runHttp($http, new ServerRequest([], [], '/', 'GET'));
        $this->assertEquals('Home', $this->responseEmitter->output);

        $this->runHttp($http, new ServerRequest([], [], '/api/hello', 'GET'));
        $this->assertEquals("Hello\nAuth check", $this->responseEmitter->output);
    }

    /**
     * @test
     */
    public function injects_route_parameters_in_controllers()
    {
        $http = router([
            '/{name}' => function ($name) {
                return new SimpleResponse('Hello ' . $name);
            },
        ]);

        $this->runHttp($http, new ServerRequest([], [], '/john', 'GET'));
        $this->assertEquals('Hello john', $this->responseEmitter->output);
    }

    /**
     * @test
     */
    public function let_router_controllers_return_string_response()
    {
        $http = router([
            '/' => function () {
                return 'Hello world!';
            },
        ]);

        $this->runHttp($http, new ServerRequest([], [], '/', 'GET'));
        $this->assertEquals('Hello world!', $this->responseEmitter->output);
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage No HTTP stack was defined
     */
    public function http_app_fails_if_no_stack_defined()
    {
        (new Application)->http()->run();
    }

    private function runHttp($http, ServerRequestInterface $request = null) : Application
    {
        $app = new Application([], 'prod', $http);
        $app->addConfig([
            EmitterInterface::class => $this->responseEmitter,
        ]);
        $app->http()->run($request);
        return $app;
    }
}
