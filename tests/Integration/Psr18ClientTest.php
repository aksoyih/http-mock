<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Tests\Integration;

use Aksoyih\HttpMock\HttpMock;
use Aksoyih\HttpMock\Testing\MocksHttp;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class Psr18ClientTest extends TestCase
{
    use MocksHttp;

    private Psr17Factory $factory;

    protected function setUp(): void
    {
        $this->factory = new Psr17Factory();
    }

    #[Test]
    public function it_returns_json_response_for_array_body(): void
    {
        HttpMock::when('GET', '/users')
            ->willReturn(['id' => 1, 'name' => 'John'])
            ->withStatus(200)
            ->register();

        $client = HttpMock::createClient();
        $request = $this->factory->createRequest('GET', '/users');
        $response = $client->sendRequest($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertSame('{"id":1,"name":"John"}', (string) $response->getBody());
    }

    #[Test]
    public function it_returns_string_body_as_is(): void
    {
        HttpMock::when('GET', '/hello')
            ->willReturn('Hello, World!')
            ->register();

        $client = HttpMock::createClient();
        $request = $this->factory->createRequest('GET', '/hello');
        $response = $client->sendRequest($request);

        $this->assertSame('Hello, World!', (string) $response->getBody());
    }

    #[Test]
    public function it_returns_empty_body_for_null(): void
    {
        HttpMock::when('DELETE', '/users/1')
            ->willReturn(null)
            ->withStatus(204)
            ->register();

        $client = HttpMock::createClient();
        $request = $this->factory->createRequest('DELETE', '/users/1');
        $response = $client->sendRequest($request);

        $this->assertSame(204, $response->getStatusCode());
        $this->assertSame('', (string) $response->getBody());
    }

    #[Test]
    public function it_includes_custom_headers(): void
    {
        HttpMock::when('GET', '/users')
            ->willReturn([])
            ->withHeaders(['X-Request-Id' => 'abc-123'])
            ->register();

        $client = HttpMock::createClient();
        $request = $this->factory->createRequest('GET', '/users');
        $response = $client->sendRequest($request);

        $this->assertSame('abc-123', $response->getHeaderLine('X-Request-Id'));
    }

    #[Test]
    public function it_records_requests(): void
    {
        HttpMock::when('POST', '/users')
            ->willReturn(['id' => 1])
            ->withStatus(201)
            ->register();

        $client = HttpMock::createClient();
        $request = $this->factory->createRequest('POST', '/users')
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->factory->createStream('{"name":"John"}'));
        $client->sendRequest($request);

        $this->addToAssertionCount(1);
        HttpMock::assertCalled('POST', '/users');
    }
}
