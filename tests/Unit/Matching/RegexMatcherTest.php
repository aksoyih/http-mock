<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Tests\Unit\Matching;

use Aksoyih\HttpMock\Matching\RegexMatcher;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RegexMatcherTest extends TestCase
{
    #[Test]
    public function it_matches_regex_pattern(): void
    {
        $matcher = new RegexMatcher('GET', '#^/users/\d+$#');
        $this->assertTrue($matcher->matches('GET', '/users/123'));
        $this->assertTrue($matcher->matches('GET', '/users/999'));
        $this->assertFalse($matcher->matches('GET', '/users/abc'));
    }

    #[Test]
    public function it_is_case_insensitive_for_method(): void
    {
        $matcher = new RegexMatcher('get', '#^/users$#');
        $this->assertTrue($matcher->matches('GET', '/users'));
    }

    #[Test]
    public function it_strips_query_string(): void
    {
        $matcher = new RegexMatcher('GET', '#^/users/\d+$#');
        $this->assertTrue($matcher->matches('GET', '/users/123?include=posts'));
    }

    #[Test]
    public function it_does_not_match_different_method(): void
    {
        $matcher = new RegexMatcher('GET', '#^/users$#');
        $this->assertFalse($matcher->matches('POST', '/users'));
    }

    #[Test]
    public function it_describes_itself(): void
    {
        $matcher = new RegexMatcher('GET', '#^/users/\d+$#');
        $this->assertSame('GET #^/users/\d+$#', $matcher->describe());
    }
}
