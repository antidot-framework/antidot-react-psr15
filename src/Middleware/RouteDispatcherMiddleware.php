<?php

declare(strict_types=1);

namespace Antidot\React\PSR15\Middleware;

use Antidot\Application\Http\Middleware\PipedRouteMiddleware;
use Antidot\Application\Http\Router;
use Antidot\React\PSR15\Response\PromiseResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use React\Promise\FulfilledPromise;

final class RouteDispatcherMiddleware implements MiddlewareInterface
{
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $promise = new FulfilledPromise();

        return new PromiseResponse(
            $promise
                ->then(function () use ($request) {
                    return $this->router->match($request);
                })
                ->then(static function (PipedRouteMiddleware $route) use ($request, $handler) {
                    if (true === $route->isFail()) {
                        return $handler->handle($request);
                    }

                    return $route->process($request, $handler);
                })
        );
    }
}
