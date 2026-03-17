<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Tests\Integration;

use Aksoyih\HttpMock\HttpMock;
use Aksoyih\HttpMock\Testing\MocksHttp;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SymfonyClientTest extends TestCase
{
    use MocksHttp;

    #[Test]
    public function it_returns_response(): void
    {
        HttpMock::when('GET', '/users')
            ->willReturn(['id' => 1])
            ->withStatus(200)
            ->register();

        $client = HttpMock::createSymfonyClient();
        $response = $client->request('GET', '/users');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('{"id":1}', $response->getContent());
    }

    #[Test]
    public function it_returns_custom_status_and_headers(): void
    {
        HttpMock::when('POST', '/users')
            ->willReturn(['created' => true])
            ->withStatus(201)
            ->withHeaders(['X-Custom' => 'value'])
            ->register();

        $client = HttpMock::createSymfonyClient();
        $response = $client->request('POST', '/users');

        $this->assertSame(201, $response->getStatusCode());
        $headers = $response->getHeaders();
        $this->assertSame(['value'], $headers['x-custom'] ?? $headers['X-Custom']);
    }

    #[Test]
    public function it_records_requests(): void
    {
        HttpMock::when('DELETE', '/items/1')
            ->willReturn(null)
            ->withStatus(204)
            ->register();

        $client = HttpMock::createSymfonyClient();
        $client->request('DELETE', '/items/1');

        $this->addToAssertionCount(1);
        HttpMock::assertCalled('DELETE', '/items/1');
    }
}
