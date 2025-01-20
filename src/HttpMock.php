<?php

namespace Aksoyih\HttpMock;

class HttpMock
{
    private array $mockResponses = [];
    private ?int $delay = null;
    private ?string $globalError = null;

    public function when(string $method, string $url): MockBuilder
    {
        return new MockBuilder($this, $method, $url);
    }

    public function withDelay(int $milliseconds): self
    {
        $this->delay = $milliseconds;
        return $this;
    }

    public function withGlobalError(string $error): self
    {
        $this->globalError = $error;
        return $this;
    }

    public function reset(): void
    {
        $this->mockResponses = [];
        $this->delay = null;
        $this->globalError = null;
    }

    public function addMockResponse(string $method, string $url, array $response): void
    {
        $this->mockResponses[$this->createKey($method, $url)] = $response;
    }

    public function getMockResponse(string $method, string $url): ?array
    {
        if ($this->globalError) {
            throw new \RuntimeException($this->globalError);
        }

        if ($this->delay) {
            usleep($this->delay * 1000);
        }

        $key = $this->createKey($method, $url);
        return $this->mockResponses[$key] ?? null;
    }

    private function createKey(string $method, string $url): string
    {
        return strtoupper($method) . '|' . $url;
    }
} 