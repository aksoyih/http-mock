<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Tests\Unit;

use Aksoyih\HttpMock\Expectation;
use Aksoyih\HttpMock\Matching\ExactMatcher;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ExpectationTest extends TestCase
{
    #[Test]
    public function it_holds_matcher_and_response(): void
    {
        $matcher = new ExactMatcher('GET', '/users');
        $expectation = new Expectation(
            matcher: $matcher,
            status: 200,
            headers: ['X-Custom' => 'value'],
            body: ['id' => 1],
        );
        $this->assertSame($matcher, $expectation->matcher);
        $this->assertSame(200, $expectation->status);
        $this->assertSame(['X-Custom' => 'value'], $expectation->headers);
        $this->assertSame(['id' => 1], $expectation->body);
    }

    #[Test]
    public function it_has_no_constraints_by_default(): void
    {
        $expectation = new Expectation(matcher: new ExactMatcher('GET', '/users'));
        $this->assertFalse($expectation->hasConstraints());
        $this->assertNull($expectation->times);
        $this->assertNull($expectation->atLeast);
        $this->assertNull($expectation->atMost);
    }

    #[Test]
    public function it_holds_verification_constraints(): void
    {
        $expectation = new Expectation(matcher: new ExactMatcher('GET', '/users'), times: 3);
        $this->assertTrue($expectation->hasConstraints());
        $this->assertSame(3, $expectation->times);
    }

    #[Test]
    public function it_validates_conflicting_constraints(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Expectation(matcher: new ExactMatcher('GET', '/users'), atLeast: 5, atMost: 2);
    }

    #[Test]
    public function it_validates_times_not_mixed_with_range(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Expectation(matcher: new ExactMatcher('GET', '/users'), times: 3, atLeast: 1);
    }

    #[Test]
    public function it_tracks_match_count(): void
    {
        $expectation = new Expectation(matcher: new ExactMatcher('GET', '/users'));
        $this->assertSame(0, $expectation->matchCount);
        $expectation->incrementMatchCount();
        $expectation->incrementMatchCount();
        $this->assertSame(2, $expectation->matchCount);
    }

    #[Test]
    public function it_defaults_to_200_with_empty_response(): void
    {
        $expectation = new Expectation(matcher: new ExactMatcher('GET', '/users'));
        $this->assertSame(200, $expectation->status);
        $this->assertSame([], $expectation->headers);
        $this->assertNull($expectation->body);
    }
}
