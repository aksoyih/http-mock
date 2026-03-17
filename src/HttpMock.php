<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock;

use Aksoyih\HttpMock\Adapter\GuzzleHandler;
use Aksoyih\HttpMock\Adapter\Psr18Client;
use Aksoyih\HttpMock\Adapter\SymfonyClient;
use Aksoyih\HttpMock\Response\ResponseBuilder;
use Closure;

final class HttpMock
{
    public const int throw = MockEngine::THROW;
    public const int DEFAULT_404 = MockEngine::DEFAULT_404;

    private static ?MockEngine $engine = null;

    private function __construct()
    {
    }

    public static function when(string $method, string $url): ResponseBuilder
    {
        return self::engine()->when($method, $url);
    }

    public static function whenMatching(Closure $callback): ResponseBuilder
    {
        return self::engine()->whenMatching($callback);
    }

    /**
     * @param array<string, array{status?: int, headers?: array<string, string|string[]>, body?: mixed}> $definitions
     */
    public static function fake(array $definitions): void
    {
        self::engine()->fake($definitions);
    }

    /**
     * @param array<string, string|string[]> $headers
     * @return array{status: int, headers: array<string, string|string[]>, body: mixed}
     */
    public static function response(mixed $body, int $status = 200, array $headers = []): array
    {
        return MockEngine::response($body, $status, $headers);
    }

    public static function createClient(): Psr18Client
    {
        return new Psr18Client(self::engine());
    }

    public static function createGuzzleHandler(): GuzzleHandler
    {
        return new GuzzleHandler(self::engine());
    }

    public static function createSymfonyClient(): SymfonyClient
    {
        return new SymfonyClient(self::engine());
    }

    public static function onUnmatched(int|Closure $strategy): void
    {
        self::engine()->onUnmatched($strategy);
    }

    public static function verify(): void
    {
        self::engine()->verify();
    }

    public static function assertCalled(string $method, string $url): void
    {
        self::engine()->assertCalled($method, $url);
    }

    public static function assertCalledTimes(string $method, string $url, int $times): void
    {
        self::engine()->assertCalledTimes($method, $url, $times);
    }

    public static function assertNotCalled(string $method, string $url): void
    {
        self::engine()->assertNotCalled($method, $url);
    }

    public static function reset(): void
    {
        self::$engine = null;
    }

    public static function scoped(Closure $callback): void
    {
        try {
            $callback();
        } finally {
            self::reset();
        }
    }

    public static function engine(): MockEngine
    {
        return self::$engine ??= new MockEngine();
    }
}
