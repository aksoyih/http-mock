<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Adapter;

use Aksoyih\HttpMock\MockEngine;

final class Psr18Client
{
    public function __construct(
        private readonly MockEngine $engine,
    ) {}
}
