<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Tests\Feature;

use Aksoyih\HttpMock\Exception\UnmatchedRequestException;
use Aksoyih\HttpMock\Exception\VerificationException;
use Aksoyih\HttpMock\HttpMock;
use Aksoyih\HttpMock\Testing\MocksHttp;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HttpMockTest extends TestCase
{
    use MocksHttp;

    private Psr17Factory $factory;

    protected function setUp(): void
    {
        $this->factory = new Psr17Factory();
    }

    #[Test]
    public function basic_when_and_register_flow(): void
    {
        HttpMock::when('GET', '/users')
            ->willReturn(['id' => 1])
            ->withStatus(200)
            ->register();

        $client = HttpMock::createClient();
        $response = $client->sendRequest($this->factory->createRequest('GET', '/users'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('{"id":1}', (string) $response->getBody());
    }

    #[Test]
    public function wildcard_matching(): void
    {
        HttpMock::when('GET', '/users/*')
            ->willReturn(['id' => 42])
            ->register();

        $client = HttpMock::createClient();
        $response = $client->sendRequest($this->factory->createRequest('GET', '/users/42'));

        $this->assertSame('{"id":42}', (string) $response->getBody());
    }

    #[Test]
    public function regex_matching(): void
    {
        HttpMock::when('GET', '#^/users/\d+$#')
            ->willReturn(['found' => true])
            ->register();

        $client = HttpMock::createClient();
        $response = $client->sendRequest($this->factory->createRequest('GET', '/users/123'));

        $this->assertSame('{"found":true}', (string) $response->getBody());
    }

    #[Test]
    public function callable_matching_with_when_matching(): void
    {
        HttpMock::whenMatching(
            fn (string $method, string $url) => $method === 'POST' && str_contains($url, '/admin'),
        )->willReturn(['access' => 'granted'])
            ->register();

        $client = HttpMock::createClient();
        $response = $client->sendRequest($this->factory->createRequest('POST', '/admin/dashboard'));

        $this->assertSame('{"access":"granted"}', (string) $response->getBody());
    }

    #[Test]
    public function fake_shorthand(): void
    {
        HttpMock::fake([
            'GET /users' => HttpMock::response(['id' => 1], 200),
            'POST /users' => HttpMock::response(['created' => true], 201),
        ]);

        $client = HttpMock::createClient();

        $response = $client->sendRequest($this->factory->createRequest('GET', '/users'));
        $this->assertSame(200, $response->getStatusCode());

        $response = $client->sendRequest($this->factory->createRequest('POST', '/users'));
        $this->assertSame(201, $response->getStatusCode());
    }

    #[Test]
    public function verify_passes_when_expectations_met(): void
    {
        HttpMock::when('GET', '/users')
            ->willReturn([])
            ->expect(times: 2)
            ->register();

        $client = HttpMock::createClient();
        $client->sendRequest($this->factory->createRequest('GET', '/users'));
        $client->sendRequest($this->factory->createRequest('GET', '/users'));

        HttpMock::verify();
        $this->assertTrue(true);
    }

    #[Test]
    public function verify_throws_when_expectations_not_met(): void
    {
        HttpMock::when('GET', '/users')
            ->willReturn([])
            ->expect(times: 2)
            ->register();

        $this->expectException(VerificationException::class);
        $this->expectExceptionMessage('expected exactly 2');

        HttpMock::verify();
    }

    #[Test]
    public function assert_called(): void
    {
        HttpMock::when('GET', '/users')
            ->willReturn([])
            ->register();

        $client = HttpMock::createClient();
        $client->sendRequest($this->factory->createRequest('GET', '/users'));

        HttpMock::assertCalled('GET', '/users');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function assert_not_called(): void
    {
        HttpMock::when('GET', '/users')
            ->willReturn([])
            ->register();

        HttpMock::assertNotCalled('GET', '/users');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function assert_called_times(): void
    {
        HttpMock::when('GET', '/users')
            ->willReturn([])
            ->register();

        $client = HttpMock::createClient();
        $client->sendRequest($this->factory->createRequest('GET', '/users'));
        $client->sendRequest($this->factory->createRequest('GET', '/users'));
        $client->sendRequest($this->factory->createRequest('GET', '/users'));

        HttpMock::assertCalledTimes('GET', '/users', 3);
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function strict_mode_throws_on_unmatched(): void
    {
        $client = HttpMock::createClient();

        $this->expectException(UnmatchedRequestException::class);
        $this->expectExceptionMessage('No mock defined for GET /unknown');

        $client->sendRequest($this->factory->createRequest('GET', '/unknown'));
    }

    #[Test]
    public function default_404_mode(): void
    {
        HttpMock::onUnmatched(HttpMock::DEFAULT_404);

        $client = HttpMock::createClient();
        $response = $client->sendRequest($this->factory->createRequest('GET', '/unknown'));

        $this->assertSame(404, $response->getStatusCode());
    }

    #[Test]
    public function callable_unmatched_handler(): void
    {
        HttpMock::onUnmatched(fn () => ['status' => 503, 'headers' => [], 'body' => 'down']);

        $client = HttpMock::createClient();
        $response = $client->sendRequest($this->factory->createRequest('GET', '/anything'));

        $this->assertSame(503, $response->getStatusCode());
        $this->assertSame('down', (string) $response->getBody());
    }

    #[Test]
    public function reset_clears_all_state(): void
    {
        HttpMock::when('GET', '/users')->willReturn([])->register();
        HttpMock::onUnmatched(HttpMock::DEFAULT_404);

        HttpMock::reset();

        $client = HttpMock::createClient();
        $this->expectException(UnmatchedRequestException::class);
        $client->sendRequest($this->factory->createRequest('GET', '/users'));
    }

    #[Test]
    public function scoped_resets_after_closure(): void
    {
        HttpMock::scoped(function () {
            HttpMock::when('GET', '/users')->willReturn([])->register();
            $client = HttpMock::createClient();
            $client->sendRequest($this->factory->createRequest('GET', '/users'));
            HttpMock::assertCalled('GET', '/users');
        });

        $this->expectException(UnmatchedRequestException::class);
        $client = HttpMock::createClient();
        $client->sendRequest($this->factory->createRequest('GET', '/users'));
    }

    #[Test]
    public function scoped_resets_even_on_exception(): void
    {
        try {
            HttpMock::scoped(function () {
                HttpMock::when('GET', '/users')->willReturn([])->register();
                throw new \RuntimeException('boom');
            });
        } catch (\RuntimeException) {
            // expected
        }

        $this->expectException(UnmatchedRequestException::class);
        $client = HttpMock::createClient();
        $client->sendRequest($this->factory->createRequest('GET', '/users'));
    }

    #[Test]
    public function last_registered_wins(): void
    {
        HttpMock::when('GET', '/users')
            ->willReturn(['source' => 'first'])
            ->register();

        HttpMock::when('GET', '/users')
            ->willReturn(['source' => 'second'])
            ->register();

        $client = HttpMock::createClient();
        $response = $client->sendRequest($this->factory->createRequest('GET', '/users'));

        $this->assertSame('{"source":"second"}', (string) $response->getBody());
    }

    #[Test]
    public function fake_with_wildcard_patterns(): void
    {
        HttpMock::fake([
            'GET /users/*' => HttpMock::response(['found' => true], 200),
        ]);

        $client = HttpMock::createClient();
        $response = $client->sendRequest($this->factory->createRequest('GET', '/users/42'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('{"found":true}', (string) $response->getBody());
    }

    #[Test]
    public function unmatched_exception_lists_defined_mocks(): void
    {
        HttpMock::when('GET', '/users')
            ->willReturn([])
            ->register();

        $client = HttpMock::createClient();

        try {
            $client->sendRequest($this->factory->createRequest('GET', '/unknown'));
            $this->fail('Expected UnmatchedRequestException');
        } catch (UnmatchedRequestException $e) {
            $this->assertStringContainsString('Defined mocks:', $e->getMessage());
            $this->assertStringContainsString('GET /users', $e->getMessage());
        }
    }

    #[Test]
    public function callable_matcher_receives_full_url_with_query_string(): void
    {
        $capturedUrl = null;

        HttpMock::whenMatching(function (string $method, string $url) use (&$capturedUrl) {
            $capturedUrl = $url;
            return true;
        })->willReturn([])->register();

        $client = HttpMock::createClient();
        $client->sendRequest($this->factory->createRequest('GET', '/users?page=2&sort=name'));

        $this->assertStringContainsString('page=2', $capturedUrl);
    }
}
