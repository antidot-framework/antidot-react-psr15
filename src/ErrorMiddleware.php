<?php

declare(strict_types=1);

namespace Antidot\React\PSR15;

use Antidot\React\PSR15\Response\PromiseResponse;
use ErrorException;
use Franzl\Middleware\Whoops\WhoopsMiddleware;
use Franzl\Middleware\Whoops\WhoopsRunner;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use React\Http\Response;
use React\Promise\Promise;
use Throwable;

class ErrorMiddleware implements MiddlewareInterface
{
    /** @var bool  */
    private $debug;

    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $promise = new Promise(function ($resolver, $canceller) {
            $this->setErrorHandler();
            $resolver();
        });

        return new PromiseResponse($promise->then(function () use ($request, $handler) {
            try {
                if ($this->debug && class_exists(WhoopsMiddleware::class)) {
                    $whoopsMiddleware = new WhoopsMiddleware();
                    return $whoopsMiddleware->process($request, $handler);
                }

                return $handler->handle($request);
            } catch (Throwable $exception) {
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
                // Error is not in mask
                return false;
            }
            throw new ErrorException($errorString, 0, $errorNumber, $errorFile, $errorLine);
        };

        set_error_handler($handler);
    }

    private function getErrorResponse(Throwable $exeption, ServerRequestInterface $request): ResponseInterface
    {
        restore_error_handler();

        if ($this->debug && class_exists(WhoopsRunner::class)) {
            $whoops = new WhoopsRunner();
            return $whoops->handle($exeption, $request);
        }

        return new Response(500, [], 'Unexpected Server Error Occurred');
    }
}
