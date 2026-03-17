<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Tests\Unit;

use Aksoyih\HttpMock\Expectation;
use Aksoyih\HttpMock\Matching\ExactMatcher;
use Aksoyih\HttpMock\Matching\WildcardMatcher;
use Aksoyih\HttpMock\MockRegistry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MockRegistryTest extends TestCase
{
    private MockRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new MockRegistry();
    }

    #[Test]
    public function it_registers_and_matches_expectations(): void
    {
        $expectation = new Expectation(matcher: new ExactMatcher('GET', '/users'), body: ['id' => 1]);
        $this->registry->add($expectation);
        $result = $this->registry->match('GET', '/users');
        $this->assertSame($expectation, $result);
    }

    #[Test]
    public function it_returns_null_when_no_match(): void
    {
        $this->assertNull($this->registry->match('GET', '/users'));
    }

    #[Test]
    public function last_registered_wins(): void
    {
        $first = new Expectation(matcher: new ExactMatcher('GET', '/users'), body: 'first');
        $second = new Expectation(matcher: new ExactMatcher('GET', '/users'), body: 'second');
        $this->registry->add($first);
        $this->registry->add($second);
        $this->assertSame($second, $this->registry->match('GET', '/users'));
    }

    #[Test]
    public function it_increments_match_count(): void
    {
        $expectation = new Expectation(matcher: new ExactMatcher('GET', '/users'));
        $this->registry->add($expectation);
        $this->registry->match('GET', '/users');
        $this->registry->match('GET', '/users');
        $this->assertSame(2, $expectation->matchCount);
    }

    #[Test]
    public function it_returns_all_expectations(): void
    {
        $e1 = new Expectation(matcher: new ExactMatcher('GET', '/a'));
        $e2 = new Expectation(matcher: new WildcardMatcher('POST', '/b/*'));
        $this->registry->add($e1);
        $this->registry->add($e2);
        $this->assertCount(2, $this->registry->all());
    }

    #[Test]
    public function it_resets(): void
    {
        $this->registry->add(new Expectation(matcher: new ExactMatcher('GET', '/a')));
        $this->registry->reset();
        $this->assertCount(0, $this->registry->all());
        $this->assertNull($this->registry->match('GET', '/a'));
    }
}
