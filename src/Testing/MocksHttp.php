<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Testing;

use Aksoyih\HttpMock\HttpMock;
use PHPUnit\Framework\Attributes\After;

trait MocksHttp
{
    #[After]
    protected function resetHttpMock(): void
    {
        HttpMock::reset();
    }
}
