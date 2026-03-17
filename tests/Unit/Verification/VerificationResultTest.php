<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Tests\Unit\Verification;

use Aksoyih\HttpMock\Expectation;
use Aksoyih\HttpMock\Matching\ExactMatcher;
use Aksoyih\HttpMock\Verification\VerificationResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class VerificationResultTest extends TestCase
{
    #[Test]
    public function it_passes_when_times_constraint_met(): void
    {
        $expectation = new Expectation(matcher: new ExactMatcher('GET', '/users'), times: 2);
        $expectation->incrementMatchCount();
        $expectation->incrementMatchCount();
        $result = VerificationResult::check($expectation, actualCount: 2);
        $this->assertTrue($result->passed);
        $this->assertSame([], $result->failures);
    }

    #[Test]
    public function it_fails_when_times_constraint_not_met(): void
    {
        $result = VerificationResult::check(
            new Expectation(matcher: new ExactMatcher('GET', '/users'), times: 2), actualCount: 0,
        );
        $this->assertFalse($result->passed);
        $this->assertStringContainsString('expected exactly 2', $result->failures[0]);
    }

    #[Test]
    public function it_fails_when_at_least_not_met(): void
    {
        $result = VerificationResult::check(
            new Expectation(matcher: new ExactMatcher('POST', '/users'), atLeast: 1), actualCount: 0,
        );
        $this->assertFalse($result->passed);
        $this->assertStringContainsString('expected at least 1', $result->failures[0]);
    }

    #[Test]
    public function it_fails_when_at_most_exceeded(): void
    {
        $result = VerificationResult::check(
            new Expectation(matcher: new ExactMatcher('DELETE', '/users'), atMost: 1), actualCount: 3,
        );
        $this->assertFalse($result->passed);
        $this->assertStringContainsString('expected at most 1', $result->failures[0]);
    }

    #[Test]
    public function it_passes_with_no_constraints(): void
    {
        $result = VerificationResult::check(
            new Expectation(matcher: new ExactMatcher('GET', '/users')), actualCount: 5,
        );
        $this->assertTrue($result->passed);
    }

    #[Test]
    public function it_passes_when_at_least_and_at_most_range_met(): void
    {
        $result = VerificationResult::check(
            new Expectation(matcher: new ExactMatcher('GET', '/users'), atLeast: 1, atMost: 5), actualCount: 3,
        );
        $this->assertTrue($result->passed);
    }

    #[Test]
    public function it_fails_when_below_at_least_in_range(): void
    {
        $result = VerificationResult::check(
            new Expectation(matcher: new ExactMatcher('GET', '/users'), atLeast: 2, atMost: 5), actualCount: 0,
        );
        $this->assertFalse($result->passed);
        $this->assertStringContainsString('expected at least 2', $result->failures[0]);
    }

    #[Test]
    public function it_fails_when_above_at_most_in_range(): void
    {
        $result = VerificationResult::check(
            new Expectation(matcher: new ExactMatcher('GET', '/users'), atLeast: 1, atMost: 3), actualCount: 6,
        );
        $this->assertFalse($result->passed);
        $this->assertStringContainsString('expected at most 3', $result->failures[0]);
    }
}
