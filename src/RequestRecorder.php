<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock;

use Aksoyih\HttpMock\Matching\RequestMatcherInterface;

final class RequestRecorder
{
    /** @var list<array{method: string, url: string, headers: array<string, string[]>, body: mixed}> */
    private array $requests = [];

    /**
     * @param array<string, string[]> $headers
     */
    public function record(string $method, string $url, array $headers, mixed $body): void
    {
        $this->requests[] = [
            'method' => strtoupper($method),
            'url' => $url,
            'headers' => $headers,
            'body' => $body,
        ];
    }

    public function countMatching(RequestMatcherInterface $matcher): int
    {
        $count = 0;
        foreach ($this->requests as $request) {
            if ($matcher->matches($request['method'], $request['url'], $request['headers'], $request['body'])) {
                $count++;
            }
        }
        return $count;
    }

    public function wasCalled(RequestMatcherInterface $matcher): bool
    {
        return $this->countMatching($matcher) > 0;
    }

    /**
     * @return list<array{method: string, url: string, headers: array<string, string[]>, body: mixed}>
     */
    public function all(): array
    {
        return $this->requests;
    }

    public function reset(): void
    {
        $this->requests = [];
    }
}
