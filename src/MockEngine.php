<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock;

use Aksoyih\HttpMock\Exception\UnmatchedRequestException;
use Aksoyih\HttpMock\Exception\VerificationException;
use Aksoyih\HttpMock\Matching\CallableMatcher;
use Aksoyih\HttpMock\Matching\ExactMatcher;
use Aksoyih\HttpMock\Matching\RegexMatcher;
use Aksoyih\HttpMock\Matching\RequestMatcherInterface;
use Aksoyih\HttpMock\Matching\WildcardMatcher;
use Aksoyih\HttpMock\Response\ResponseBuilder;
use Aksoyih\HttpMock\Verification\VerificationResult;
use Closure;

final class MockEngine
{
    public const int THROW = 0;
    public const int DEFAULT_404 = 1;

    private MockRegistry $registry;
    private RequestRecorder $recorder;
    private int|Closure $unmatchedStrategy = self::THROW;

    public function __construct()
    {
        $this->registry = new MockRegistry();
        $this->recorder = new RequestRecorder();
    }

    public function when(string $method, string $url): ResponseBuilder
    {
        $matcher = $this->detectMatcher($method, $url);
        return new ResponseBuilder($this->registry, $matcher);
    }

    public function whenMatching(Closure $callback): ResponseBuilder
    {
        return new ResponseBuilder($this->registry, new CallableMatcher($callback));
    }

    /**
     * @param array<string, array{status?: int, headers?: array<string, string|string[]>, body?: mixed}> $definitions
     */
    public function fake(array $definitions): void
    {
        foreach ($definitions as $key => $response) {
            [$method, $url] = explode(' ', $key, 2);
            $matcher = $this->detectMatcher($method, $url);
            $this->registry->add(new Expectation(
                matcher: $matcher,
                status: $response['status'] ?? 200,
                headers: $response['headers'] ?? [],
                body: $response['body'] ?? null,
            ));
        }
    }

    /**
     * @param array<string, string|string[]> $headers
     * @return array{status: int, headers: array<string, string|string[]>, body: mixed}
     */
    public static function response(mixed $body, int $status = 200, array $headers = []): array
    {
        return ['status' => $status, 'headers' => $headers, 'body' => $body];
    }

    /**
     * @param array<string, string[]> $headers
     * @return array{status: int, headers: array<string, string|string[]>, body: mixed}
     */
    public function handleRequest(string $method, string $url, array $headers = [], mixed $body = null): array
    {
        $this->recorder->record($method, $url, $headers, $body);
        $expectation = $this->registry->match($method, $url, $headers, $body);

        if ($expectation !== null) {
            return [
                'status' => $expectation->status,
                'headers' => $expectation->headers,
                'body' => $expectation->body,
            ];
        }

        return $this->handleUnmatched($method, $url);
    }

    public function onUnmatched(int|Closure $strategy): void
    {
        $this->unmatchedStrategy = $strategy;
    }

    public function verify(): void
    {
        $failures = [];
        foreach ($this->registry->all() as $expectation) {
            $count = $this->recorder->countMatching($expectation->matcher);
            $result = VerificationResult::check($expectation, $count);
            if (!$result->passed) {
                $failures = [...$failures, ...$result->failures];
            }
        }
        if ($failures !== []) {
            throw VerificationException::fromFailures($failures);
        }
    }

    public function assertCalled(string $method, string $url): void
    {
        $matcher = $this->detectMatcher($method, $url);
        if (!$this->recorder->wasCalled($matcher)) {
            throw VerificationException::fromFailures([
                sprintf('%s: expected at least 1 call(s), got 0', $matcher->describe()),
            ]);
        }
    }

    public function assertCalledTimes(string $method, string $url, int $times): void
    {
        $matcher = $this->detectMatcher($method, $url);
        $actual = $this->recorder->countMatching($matcher);
        if ($actual !== $times) {
            throw VerificationException::fromFailures([
                sprintf('%s: expected exactly %d call(s), got %d', $matcher->describe(), $times, $actual),
            ]);
        }
    }

    public function assertNotCalled(string $method, string $url): void
    {
        $matcher = $this->detectMatcher($method, $url);
        if ($this->recorder->wasCalled($matcher)) {
            $count = $this->recorder->countMatching($matcher);
            throw VerificationException::fromFailures([
                sprintf('%s: expected 0 calls, got %d', $matcher->describe(), $count),
            ]);
        }
    }

    /**
     * @param array{status: int, headers: array<string, string|string[]>, body: mixed} $result
     * @return array{status: int, headers: array<string, string|string[]>, body: string}
     */
    public static function serializeResponse(array $result): array
    {
        $body = $result['body'];
        $headers = $result['headers'];

        if (is_array($body)) {
            $headers['Content-Type'] ??= 'application/json';
            $body = json_encode($body, JSON_THROW_ON_ERROR);
        } elseif ($body === null) {
            $body = '';
        } elseif (is_scalar($body)) {
            $body = (string) $body;
        } else {
            $body = '';
        }

        return ['status' => $result['status'], 'headers' => $headers, 'body' => $body];
    }

    public function registry(): MockRegistry
    {
        return $this->registry;
    }

    public function recorder(): RequestRecorder
    {
        return $this->recorder;
    }

    public function reset(): void
    {
        $this->registry->reset();
        $this->recorder->reset();
        $this->unmatchedStrategy = self::THROW;
    }

    private function detectMatcher(string $method, string $url): RequestMatcherInterface
    {
        if (str_starts_with($url, '#') && str_ends_with($url, '#')) {
            return new RegexMatcher($method, $url);
        }
        if (str_contains($url, '*')) {
            return new WildcardMatcher($method, $url);
        }
        return new ExactMatcher($method, $url);
    }

    /**
     * @return array{status: int, headers: array<string, string|string[]>, body: mixed}
     */
    private function handleUnmatched(string $method, string $url): array
    {
        if ($this->unmatchedStrategy instanceof Closure) {
            /** @var array{status: int, headers: array<string, string|string[]>, body: mixed} */
            return ($this->unmatchedStrategy)($method, $url);
        }
        if ($this->unmatchedStrategy === self::DEFAULT_404) {
            return ['status' => 404, 'headers' => [], 'body' => null];
        }
        throw UnmatchedRequestException::forRequest($method, $url, $this->registry->describeAll());
    }
}
