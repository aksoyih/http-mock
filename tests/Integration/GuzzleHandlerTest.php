<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Tests\Integration;

use Aksoyih\HttpMock\HttpMock;
use Aksoyih\HttpMock\Testing\MocksHttp;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GuzzleHandlerTest extends TestCase
{
    use MocksHttp;

    #[Test]
    public function it_works_with_guzzle_client(): void
    {
        HttpMock::when('GET', '/users')
            ->willReturn(['id' => 1])
            ->withStatus(200)
            ->register();

        $handler = HttpMock::createGuzzleHandler();
        $stack = HandlerStack::create($handler);
        $client = new Client(['handler' => $stack]);

        $response = $client->get('/users');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('{"id":1}', (string) $response->getBody());
    }

    #[Test]
    public function it_records_guzzle_requests(): void
    {
        HttpMock::when('POST', '/items')
            ->willReturn(['id' => 42])
            ->withStatus(201)
            ->register();

        $handler = HttpMock::createGuzzleHandler();
        $stack = HandlerStack::create($handler);
        $client = new Client(['handler' => $stack]);

        $client->post('/items', ['json' => ['name' => 'Widget']]);

        $this->addToAssertionCount(1);
        HttpMock::assertCalled('POST', '/items');
    }

    #[Test]
    public function it_supports_custom_status_codes(): void
    {
        HttpMock::when('DELETE', '/items/1')
            ->willReturn(null)
            ->withStatus(204)
            ->register();

        $handler = HttpMock::createGuzzleHandler();
        $stack = HandlerStack::create($handler);
        $client = new Client(['handler' => $stack, 'http_errors' => false]);

        $response = $client->delete('/items/1');

        $this->assertSame(204, $response->getStatusCode());
    }
}
