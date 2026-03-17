<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Tests\Unit\Matching;

use Aksoyih\HttpMock\Matching\CallableMatcher;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CallableMatcherTest extends TestCase
{
    #[Test]
    public function it_delegates_to_callable(): void
    {
        $matcher = new CallableMatcher(
            fn(string $method, string $url) => $method === 'GET' && $url === '/users',
        );
        $this->assertTrue($matcher->matches('GET', '/users'));
        $this->assertFalse($matcher->matches('POST', '/users'));
    }

    #[Test]
    public function it_passes_headers_and_body(): void
    {
        $matcher = new CallableMatcher(
            fn(string $method, string $url, array $headers, mixed $body) =>
                isset($headers['Authorization']) && $body === '{"admin":true}',
        );
        $this->assertTrue($matcher->matches(
            'POST', '/admin', ['Authorization' => ['Bearer token']], '{"admin":true}',
        ));
        $this->assertFalse($matcher->matches('POST', '/admin'));
    }

    #[Test]
    public function it_describes_itself(): void
    {
        $matcher = new CallableMatcher(fn() => true);
        $this->assertSame('callable matcher', $matcher->describe());
    }
}
