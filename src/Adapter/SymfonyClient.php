<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Adapter;

use Aksoyih\HttpMock\MockEngine;

final class SymfonyClient
{
    public function __construct(
        private readonly MockEngine $engine,
    ) {}
}
