<?php

declare(strict_types=1);

namespace Antidot\React\PSR15\Middleware;

use Antidot\React\PSR15\Response\PromiseResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

use function React\Promise\resolve;

use const JSON_THROW_ON_ERROR;

class RequestLoggerMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $logger = $this->logger;
        return new PromiseResponse(resolve($request)
            ->then(static function (ServerRequestInterface $request) use ($handler, $logger) {
                $logger->debug(\json_encode([
                    'method' => $request->getMethod(),
                    'target' => $request->getRequestTarget(),
                    'headers' => $request->getHeaders(),
                    'query-string' => $request->getQueryParams(),
                    'body' => (string)$request->getBody()
                ], JSON_THROW_ON_ERROR));

                return $handler->handle($request);
            }));
    }
}
