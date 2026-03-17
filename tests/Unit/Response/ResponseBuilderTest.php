<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Tests\Unit\Response;

use Aksoyih\HttpMock\Matching\ExactMatcher;
use Aksoyih\HttpMock\Matching\RequestMatcherInterface;
use Aksoyih\HttpMock\MockRegistry;
use Aksoyih\HttpMock\Response\ResponseBuilder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ResponseBuilderTest extends TestCase
{
    private MockRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new MockRegistry();
    }

    #[Test]
    public function it_registers_expectation_with_defaults(): void
    {
        $matcher = new ExactMatcher('GET', '/users');
        (new ResponseBuilder($this->registry, $matcher))->register();
        $expectation = $this->registry->all()[0];
        $this->assertSame(200, $expectation->status);
        $this->assertSame([], $expectation->headers);
        $this->assertNull($expectation->body);
    }

    #[Test]
    public function it_builds_full_response(): void
    {
        $matcher = new ExactMatcher('GET', '/users');
        (new ResponseBuilder($this->registry, $matcher))
            ->willReturn(['id' => 1])
            ->withStatus(201)
            ->withHeaders(['X-Custom' => 'value'])
            ->register();
        $expectation = $this->registry->all()[0];
        $this->assertSame(201, $expectation->status);
        $this->assertSame(['X-Custom' => 'value'], $expectation->headers);
        $this->assertSame(['id' => 1], $expectation->body);
    }

    #[Test]
    public function it_merges_headers(): void
    {
        $matcher = new ExactMatcher('GET', '/users');
        (new ResponseBuilder($this->registry, $matcher))
            ->withHeaders(['X-First' => 'a'])
            ->withHeaders(['X-Second' => 'b'])
            ->register();
        $expectation = $this->registry->all()[0];
        $this->assertSame(['X-First' => 'a', 'X-Second' => 'b'], $expectation->headers);
    }

    #[Test]
    public function it_sets_verification_constraints(): void
    {
        $matcher = new ExactMatcher('GET', '/users');
        (new ResponseBuilder($this->registry, $matcher))
            ->willReturn([])
            ->expect(times: 2)
            ->register();
        $expectation = $this->registry->all()[0];
        $this->assertSame(2, $expectation->times);
    }

    #[Test]
    public function it_sets_at_least_and_at_most(): void
    {
        $matcher = new ExactMatcher('GET', '/users');
        (new ResponseBuilder($this->registry, $matcher))
            ->expect(atLeast: 1, atMost: 5)
            ->register();
        $expectation = $this->registry->all()[0];
        $this->assertSame(1, $expectation->atLeast);
        $this->assertSame(5, $expectation->atMost);
    }

    #[Test]
    public function fluent_methods_return_self(): void
    {
        $matcher = new ExactMatcher('GET', '/users');
        $builder = new ResponseBuilder($this->registry, $matcher);
        $this->assertSame($builder, $builder->willReturn([]));
        $this->assertSame($builder, $builder->withStatus(200));
        $this->assertSame($builder, $builder->withHeaders([]));
        $this->assertSame($builder, $builder->expect(times: 1));
    }
}
