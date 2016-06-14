<?php

namespace Stratify\Framework\Test;

require_once __DIR__ . '/../.puli/GeneratedPuliFactory.php';

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Puli\Repository\Api\ResourceRepository;
use Puli\Repository\FilesystemRepository;
use Stratify\Framework\Application;
use Stratify\Framework\Test\Mock\FakeResponseEmitter;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\ServerRequest;
use function Stratify\Router\route;
use function Stratify\Framework\pipe;
use function Stratify\Framework\prefix;
use function Stratify\Framework\router;

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
        $http = function (RequestInterface $request, ResponseInterface $response, callable $next) {
            $response->getBody()->write('Hello world!');
            return $response;
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
            '/' => function () {
                return new HtmlResponse('Home');
            },
            '/about' => route(function () {
                return new HtmlResponse('About');
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
                return new HtmlResponse('Admin');
            },
            '/api' => router([
                '/api/hello' => function () {
                    return new HtmlResponse('Hello');
                },
                '/api/world' => function () {
                    return new HtmlResponse('World');
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
                '/' => function (RequestInterface $request, ResponseInterface $response) {
                    $response->getBody()->write('Home');
                    return $response;
                },
                '/api/{resource}' => pipe([
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
                return new HtmlResponse('Hello ' . $name);
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
     */
    public function routes_can_be_defined_in_external_file_using_puli_path()
    {
        $http = router('/Fixture/routes.php');

        $this->runHttp($http, new ServerRequest([], [], '/', 'GET'));
        $this->assertEquals('Hello world!', $this->responseEmitter->output);
    }

    private function runHttp($http, ServerRequestInterface $request = null)
    {
        $config = [
            EmitterInterface::class => $this->responseEmitter,
            // Override the ResourceRepository
            ResourceRepository::class => new FilesystemRepository(__DIR__),
        ];
        $app = new Application($http, [], $config);
        $app->http()->run($request);
        return $app;
    }
}
