<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Tests\Unit\Matching;

use Aksoyih\HttpMock\Matching\ExactMatcher;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ExactMatcherTest extends TestCase
{
    #[Test]
    public function it_matches_exact_method_and_url(): void
    {
        $matcher = new ExactMatcher('GET', '/users/123');
        $this->assertTrue($matcher->matches('GET', '/users/123'));
    }

    #[Test]
    public function it_is_case_insensitive_for_method(): void
    {
        $matcher = new ExactMatcher('get', '/users');
        $this->assertTrue($matcher->matches('GET', '/users'));
        $this->assertTrue($matcher->matches('get', '/users'));
    }

    #[Test]
    public function it_does_not_match_different_url(): void
    {
        $matcher = new ExactMatcher('GET', '/users/123');
        $this->assertFalse($matcher->matches('GET', '/users/456'));
    }

    #[Test]
    public function it_does_not_match_different_method(): void
    {
        $matcher = new ExactMatcher('GET', '/users');
        $this->assertFalse($matcher->matches('POST', '/users'));
    }

    #[Test]
    public function it_strips_query_string_before_matching(): void
    {
        $matcher = new ExactMatcher('GET', '/users');
        $this->assertTrue($matcher->matches('GET', '/users?page=1&limit=10'));
    }

    #[Test]
    public function it_ignores_headers_and_body(): void
    {
        $matcher = new ExactMatcher('POST', '/users');
        $this->assertTrue($matcher->matches('POST', '/users', ['Content-Type' => ['application/json']], '{"name":"John"}'));
    }

    #[Test]
    public function it_describes_itself(): void
    {
        $matcher = new ExactMatcher('GET', '/users/123');
        $this->assertSame('GET /users/123', $matcher->describe());
    }
}
