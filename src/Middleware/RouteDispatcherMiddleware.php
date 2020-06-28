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

use function React\Promise\resolve;

final class RouteDispatcherMiddleware implements MiddlewareInterface
{
    private Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return new PromiseResponse(
            resolve($this->router->match($request))
                ->then(static function (PipedRouteMiddleware $route) use ($request, $handler) {
                    if (true === $route->isFail()) {
                        return $handler->handle($request);
                    }

                    return $route->process($request, $handler);
                })
        );
    }
}
