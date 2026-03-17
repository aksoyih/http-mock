<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Adapter;

use Aksoyih\HttpMock\MockEngine;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\RequestInterface;

final readonly class GuzzleHandler
{
    private Psr17Factory $factory;

    public function __construct(
        private MockEngine $engine,
    ) {
        if (!class_exists(\GuzzleHttp\Client::class)) {
            throw new \RuntimeException(
                'guzzlehttp/guzzle is required to use GuzzleHandler. Install it with: composer require guzzlehttp/guzzle',
            );
        }
        $this->factory = new Psr17Factory();
    }

    /**
     * @param array<string, mixed> $options
     */
    public function __invoke(RequestInterface $request, array $options): PromiseInterface
    {
        $method = $request->getMethod();
        $url = (string) $request->getUri();
        $headers = $request->getHeaders();
        $body = (string) $request->getBody();

        $raw = $this->engine->handleRequest($method, $url, $headers, $body === '' ? null : $body);
        $result = MockEngine::serializeResponse($raw);

        $response = $this->factory->createResponse($result['status']);

        foreach ($result['headers'] as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        if ($result['body'] !== '') {
            $response = $response->withBody($this->factory->createStream($result['body']));
        }

        return Create::promiseFor($response);
    }
}
