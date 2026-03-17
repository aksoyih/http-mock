<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Tests\Unit\Matching;

use Aksoyih\HttpMock\Matching\WildcardMatcher;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class WildcardMatcherTest extends TestCase
{
    #[Test]
    public function single_star_matches_one_segment(): void
    {
        $matcher = new WildcardMatcher('GET', '/users/*');
        $this->assertTrue($matcher->matches('GET', '/users/123'));
        $this->assertTrue($matcher->matches('GET', '/users/abc'));
        $this->assertFalse($matcher->matches('GET', '/users/123/posts'));
    }

    #[Test]
    public function double_star_matches_multiple_segments(): void
    {
        $matcher = new WildcardMatcher('GET', '/api/**');
        $this->assertTrue($matcher->matches('GET', '/api/v1'));
        $this->assertTrue($matcher->matches('GET', '/api/v1/users/123'));
    }

    #[Test]
    public function mixed_wildcards(): void
    {
        $matcher = new WildcardMatcher('GET', '/api/*/users/**');
        $this->assertTrue($matcher->matches('GET', '/api/v2/users/123/posts'));
        $this->assertFalse($matcher->matches('GET', '/api/v1/v2/users/123'));
    }

    #[Test]
    public function it_is_case_insensitive_for_method(): void
    {
        $matcher = new WildcardMatcher('get', '/users/*');
        $this->assertTrue($matcher->matches('GET', '/users/1'));
    }

    #[Test]
    public function it_strips_query_string(): void
    {
        $matcher = new WildcardMatcher('GET', '/users/*');
        $this->assertTrue($matcher->matches('GET', '/users/123?include=posts'));
    }

    #[Test]
    public function it_does_not_match_different_method(): void
    {
        $matcher = new WildcardMatcher('GET', '/users/*');
        $this->assertFalse($matcher->matches('POST', '/users/123'));
    }

    #[Test]
    public function it_describes_itself(): void
    {
        $matcher = new WildcardMatcher('GET', '/users/*');
        $this->assertSame('GET /users/*', $matcher->describe());
    }
}
