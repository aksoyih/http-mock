<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Tests\Unit;

use Aksoyih\HttpMock\Matching\ExactMatcher;
use Aksoyih\HttpMock\RequestRecorder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RequestRecorderTest extends TestCase
{
    private RequestRecorder $recorder;

    protected function setUp(): void
    {
        $this->recorder = new RequestRecorder();
    }

    #[Test]
    public function it_records_requests(): void
    {
        $this->recorder->record('GET', '/users', [], null);
        $this->recorder->record('POST', '/users', ['Content-Type' => ['application/json']], '{"name":"John"}');
        $requests = $this->recorder->all();
        $this->assertCount(2, $requests);
        $this->assertSame('GET', $requests[0]['method']);
        $this->assertSame('/users', $requests[0]['url']);
        $this->assertSame('POST', $requests[1]['method']);
        $this->assertSame('{"name":"John"}', $requests[1]['body']);
    }

    #[Test]
    public function it_counts_matching_requests(): void
    {
        $this->recorder->record('GET', '/users', [], null);
        $this->recorder->record('GET', '/users', [], null);
        $this->recorder->record('POST', '/users', [], null);
        $matcher = new ExactMatcher('GET', '/users');
        $this->assertSame(2, $this->recorder->countMatching($matcher));
    }

    #[Test]
    public function it_checks_if_matcher_was_called(): void
    {
        $this->recorder->record('GET', '/users', [], null);
        $this->assertTrue($this->recorder->wasCalled(new ExactMatcher('GET', '/users')));
        $this->assertFalse($this->recorder->wasCalled(new ExactMatcher('DELETE', '/users')));
    }

    #[Test]
    public function it_resets(): void
    {
        $this->recorder->record('GET', '/users', [], null);
        $this->recorder->reset();
        $this->assertCount(0, $this->recorder->all());
    }
}
