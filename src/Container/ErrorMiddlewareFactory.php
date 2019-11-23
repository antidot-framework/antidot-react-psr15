<?php

declare(strict_types=1);

namespace Antidot\React\PSR15\Container;

use Antidot\React\PSR15\ErrorMiddleware;
use Psr\Container\ContainerInterface;

class ErrorMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new ErrorMiddleware($container->get('config')['debug']);
    }
}
