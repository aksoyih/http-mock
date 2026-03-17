<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Adapter;

use Aksoyih\HttpMock\MockEngine;
use Nyholm\Psr7\Factory\Psr17Factory;
use Override;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final readonly class Psr18Client implements ClientInterface
{
    private Psr17Factory $factory;

    public function __construct(
        private MockEngine $engine,
    ) {
        $this->factory = new Psr17Factory();
    }

    #[Override]
    public function sendRequest(RequestInterface $request): ResponseInterface
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

        return $response;
    }
}
