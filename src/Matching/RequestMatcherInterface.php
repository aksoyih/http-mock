<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Matching;

interface RequestMatcherInterface
{
    /**
     * @param array<string, string[]> $headers PSR-7 style headers (multi-value)
     */
    public function matches(string $method, string $url, array $headers = [], mixed $body = null): bool;

    /**
     * Human-readable description for error messages (e.g., "GET /users/123").
     */
    public function describe(): string;
}
