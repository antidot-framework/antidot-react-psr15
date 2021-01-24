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
use React\Http\Message\Response;
use Throwable;

use function React\Promise\resolve;

class ErrorMiddleware implements MiddlewareInterface
{
    private bool $debug;

    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $debug = $this->debug;

        return new PromiseResponse(
            resolve($request)
                ->then(
                    static function (ServerRequestInterface $request) use ($handler, $debug) {
                        self::setErrorHandler();
                        try {
                            if ($debug && class_exists(WhoopsMiddleware::class)) {
                                $response = resolve(new WhoopsMiddleware())
                                    ->then(
                                        static fn(WhoopsMiddleware $whoopsMiddleware) =>
                                            $whoopsMiddleware->process($request, $handler)
                                    );
                                restore_error_handler();
                                return $response;
                            }

                            $response = resolve($handler->handle($request));
                            restore_error_handler();

                            return $response;
                        } catch (Throwable $exception) {
                            restore_error_handler();
                            return resolve(self::getErrorResponse($exception, $request, $debug));
                        }
                    }
                )
        );
    }

    private static function setErrorHandler(): void
    {
        $handler = static function (
            int $errorNumber,
            string $errorString,
            string $errorFile,
            int $errorLine,
            ?array $errorContext
        ): bool {
            if (!(error_reporting() & $errorNumber)) {
                return false;
            }
            throw new ErrorException($errorString, 0, $errorNumber, $errorFile, $errorLine);
        };

        set_error_handler($handler);
    }

    private static function getErrorResponse(
        Throwable $exeption,
        ServerRequestInterface $request,
        bool $debug
    ): ResponseInterface {
        if ($debug && class_exists(WhoopsRunner::class)) {
            $whoops = new WhoopsRunner();
            return $whoops->handle($exeption, $request);
        }

        return new Response(500, [], 'Unexpected Server Error Occurred');
    }
}
