<?php

declare(strict_types=1);

namespace Antidot\React\PSR15\Middleware;

use Antidot\React\PSR15\Response\PromiseResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use React\Promise\FulfilledPromise;
use Zend\Diactoros\Response\HtmlResponse;

final class RouteNotFoundMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $promise = new FulfilledPromise();

        return new PromiseResponse($promise->then(function () {
            return new HtmlResponse('<html><head></head><body>Page not found</body></html>', 404);
        }));
    }
}
