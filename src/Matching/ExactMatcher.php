<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Matching;

use Override;

final readonly class ExactMatcher implements RequestMatcherInterface
{
    private string $method;
    private string $url;

    public function __construct(string $method, string $url)
    {
        $this->method = strtoupper($method);
        $this->url = $url;
    }

    #[Override]
    public function matches(string $method, string $url, array $headers = [], mixed $body = null): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?? $url;
        return strtoupper($method) === $this->method && $path === $this->url;
    }

    #[Override]
    public function describe(): string
    {
        return $this->method . ' ' . $this->url;
    }
}
