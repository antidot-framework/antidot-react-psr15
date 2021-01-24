<?php

declare(strict_types=1);

namespace Antidot\React\PSR15\Response;

use RingCentral\Psr7\Response;
use React\Promise\PromiseInterface;

class PromiseResponse extends Response implements PromiseInterface
{
    private PromiseInterface $promise;

    /**
     * PromiseResponse constructor.
     * @param PromiseInterface $promise
     * @param null $body
     * @param int $status
     * @param array<array<string>> $headers
     */
    public function __construct(
        PromiseInterface $promise,
        $body = null,
        int $status = 200,
        array $headers = []
    ) {
        parent::__construct($status, $headers, $body);
        $this->promise = $promise;
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        return $this->promise->then($onFulfilled, $onRejected, $onProgress);
    }

    public function promise(): PromiseInterface
    {
        return $this->promise;
    }
}
