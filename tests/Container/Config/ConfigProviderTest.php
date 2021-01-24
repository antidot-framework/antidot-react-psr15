<?php

namespace AntidotTest\React\PSR15\Container\Config;

use Antidot\React\PSR15\Container\Config\ConfigProvider;
use Antidot\React\PSR15\Container\ErrorMiddlewareFactory;
use Antidot\React\PSR15\Middleware\ErrorMiddleware;
use Antidot\React\PSR15\Middleware\ExceptionLoggerMiddleware;
use Antidot\React\PSR15\Middleware\RequestLoggerMiddleware;
use Antidot\React\PSR15\Middleware\RouteDispatcherMiddleware;
use Antidot\React\PSR15\Middleware\RouteNotFoundMiddleware;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    public function testItShouldReturnTheConfigArray(): void
    {
        $configProvider = new ConfigProvider();

        $this->assertSame([
            'services' => [
                ExceptionLoggerMiddleware::class => ExceptionLoggerMiddleware::class,
                RequestLoggerMiddleware::class => RequestLoggerMiddleware::class,
                RouteDispatcherMiddleware::class => RouteDispatcherMiddleware::class,
                RouteNotFoundMiddleware::class => RouteNotFoundMiddleware::class,
            ],
            'factories' => [
                ErrorMiddleware::class => ErrorMiddlewareFactory::class,
            ],
        ], $configProvider());
    }
}
