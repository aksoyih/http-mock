<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Matching;

use Override;

final readonly class RegexMatcher implements RequestMatcherInterface
{
    private string $method;
    private string $pattern;

    public function __construct(string $method, string $pattern)
    {
        $this->method = strtoupper($method);
        $this->pattern = $pattern;
    }

    #[Override]
    public function matches(string $method, string $url, array $headers = [], mixed $body = null): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?? $url;
        return strtoupper($method) === $this->method && preg_match($this->pattern, $path) === 1;
    }

    #[Override]
    public function describe(): string
    {
        return $this->method . ' ' . $this->pattern;
    }
}
