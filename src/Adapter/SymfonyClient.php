<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Adapter;

use Aksoyih\HttpMock\MockEngine;
use Override;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

final readonly class SymfonyClient implements HttpClientInterface
{
    public function __construct(
        private MockEngine $engine,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    #[Override]
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $headers = [];
        $optionHeaders = $options['headers'] ?? [];
        if (is_array($optionHeaders)) {
            foreach ($optionHeaders as $name => $value) {
                $headerName = is_string($name) ? $name : (string) $name;
                if (is_array($value)) {
                    $headers[$headerName] = array_map(fn (mixed $v): string => is_scalar($v) ? (string) $v : '', $value);
                } elseif (is_scalar($value)) {
                    $headers[$headerName] = [(string) $value];
                }
            }
        }

        $body = $options['body'] ?? $options['json'] ?? null;

        if (isset($options['json'])) {
            $body = json_encode($body, JSON_THROW_ON_ERROR);
        }

        $raw = $this->engine->handleRequest($method, $url, $headers, $body);
        $result = MockEngine::serializeResponse($raw);

        $mockHeaders = [];
        foreach ($result['headers'] as $name => $value) {
            $mockHeaders[] = $name . ': ' . (is_array($value) ? implode(', ', $value) : $value);
        }

        $mockResponse = new MockResponse($result['body'], [
            'http_code' => $result['status'],
            'response_headers' => $mockHeaders,
        ]);

        $innerClient = new MockHttpClient($mockResponse);

        return $innerClient->request($method, $url);
    }

    #[Override]
    public function stream(ResponseInterface|iterable $responses, ?float $timeout = null): ResponseStreamInterface
    {
        throw new \RuntimeException('Streaming is not supported by the mock client.');
    }

    /**
     * @param array<string, mixed> $options
     */
    #[Override]
    public function withOptions(array $options): static
    {
        return $this;
    }
}
