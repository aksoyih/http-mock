<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Matching;

use Closure;
use Override;

final readonly class CallableMatcher implements RequestMatcherInterface
{
    private Closure $callback;

    public function __construct(Closure $callback)
    {
        $this->callback = $callback;
    }

    #[Override]
    public function matches(string $method, string $url, array $headers = [], mixed $body = null): bool
    {
        return ($this->callback)($method, $url, $headers, $body);
    }

    #[Override]
    public function describe(): string
    {
        return 'callable matcher';
    }
}
