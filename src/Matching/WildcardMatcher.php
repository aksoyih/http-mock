<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Matching;

use Override;

final readonly class WildcardMatcher implements RequestMatcherInterface
{
    private string $method;
    private string $pattern;
    private string $regex;

    public function __construct(string $method, string $pattern)
    {
        $this->method = strtoupper($method);
        $this->pattern = $pattern;
        $this->regex = $this->compilePattern($pattern);
    }

    #[Override]
    public function matches(string $method, string $url, array $headers = [], mixed $body = null): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?? $url;
        return strtoupper($method) === $this->method && preg_match($this->regex, $path) === 1;
    }

    #[Override]
    public function describe(): string
    {
        return $this->method . ' ' . $this->pattern;
    }

    private function compilePattern(string $pattern): string
    {
        $escaped = preg_quote($pattern, '#');
        $escaped = str_replace('\\*\\*', '@@DOUBLE@@', $escaped);
        $escaped = str_replace('\\*', '[^/]+', $escaped);
        $escaped = str_replace('@@DOUBLE@@', '.*', $escaped);
        return '#^' . $escaped . '$#';
    }
}
