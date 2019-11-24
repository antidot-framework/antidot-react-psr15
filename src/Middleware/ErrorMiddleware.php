<?php

declare(strict_types=1);

namespace Antidot\React\PSR15\Middleware;

use Antidot\React\PSR15\Response\PromiseResponse;
use ErrorException;
use Franzl\Middleware\Whoops\WhoopsMiddleware;
use Franzl\Middleware\Whoops\WhoopsRunner;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use React\Http\Response;
use React\Promise\FulfilledPromise;
use React\Promise\Promise;
use Throwable;

class ErrorMiddleware implements MiddlewareInterface
{
    private $debug;

    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $promise = new FulfilledPromise();

        return new PromiseResponse($promise->then(function () use ($request, $handler) {
            $this->setErrorHandler();
            try {
                if ($this->debug && class_exists(WhoopsMiddleware::class)) {
                    $whoopsMiddleware = new WhoopsMiddleware();
                    $response = $whoopsMiddleware->process($request, $handler);
                    restore_error_handler();
                    return $response;
                }

                $response = $handler->handle($request);
                restore_error_handler();

                return $response;
            } catch (Throwable $exception) {
                restore_error_handler();
                return $this->getErrorResponse($exception, $request);
            }
        }));
    }

    private function setErrorHandler(): void
    {
        $handler = static function (
            int $errorNumber,
            string $errorString,
            string $errorFile,
            int $errorLine,
            ?array $errorContext
        ): bool {
            if (! (error_reporting() & $errorNumber)) {
                return false;
            }
            throw new ErrorException($errorString, 0, $errorNumber, $errorFile, $errorLine);
        };

        set_error_handler($handler);
    }

    private function getErrorResponse(Throwable $exeption, ServerRequestInterface $request): ResponseInterface
    {

        if ($this->debug && class_exists(WhoopsRunner::class)) {
            $whoops = new WhoopsRunner();
            return $whoops->handle($exeption, $request);
        }

        return new Response(500, [], 'Unexpected Server Error Occurred');
    }
}
