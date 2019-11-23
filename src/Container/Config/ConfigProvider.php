<?php

declare(strict_types=1);

namespace Antidot\React\PSR15\Container\Config;

use Antidot\React\PSR15\Container\ErrorMiddlewareFactory;
use Antidot\React\PSR15\Middleware\ErrorMiddleware;
use Antidot\React\PSR15\Middleware\ExceptionLoggerMiddleware;
use Antidot\React\PSR15\Middleware\RequestLoggerMiddleware;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                'invokables' => [
                    ExceptionLoggerMiddleware::class => ExceptionLoggerMiddleware::class,
                    RequestLoggerMiddleware::class => RequestLoggerMiddleware::class,
                ],
                'factories' => [
                    ErrorMiddleware::class => ErrorMiddlewareFactory::class,
                ]
            ],
        ];
    }
}
