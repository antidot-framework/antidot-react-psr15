<?php

declare(strict_types=1);

namespace Antidot\React\PSR15\Middleware;

use Antidot\React\PSR15\Response\PromiseResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use function json_encode;
use function React\Promise\resolve;

use const JSON_THROW_ON_ERROR;

class ExceptionLoggerMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        return new PromiseResponse(resolve($request)
            ->then(function (ServerRequestInterface $request) use ($handler) {
                try {
                    return $handler->handle($request);
                } catch (Throwable $exception) {
                    $this->logger->error(json_encode([
                        'message' => $exception->getMessage(),
                        'code' => $exception->getCode(),
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine()
                    ], JSON_THROW_ON_ERROR));

                    throw $exception;
                }
            }));
    }
}
