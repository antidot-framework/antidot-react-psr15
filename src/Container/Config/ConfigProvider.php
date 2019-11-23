<?php

declare(strict_types=1);

namespace Antidot\React\PSR15\Container\Config;

use Antidot\React\PSR15\Container\ErrorMiddlewareFactory;
use Antidot\React\PSR15\ErrorMiddleware;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                'factories' => [
                    ErrorMiddleware::class => ErrorMiddlewareFactory::class,
                ]
            ],
        ];
    }
}
