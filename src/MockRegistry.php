<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock;

final class MockRegistry
{
    /** @var list<Expectation> */
    private array $expectations = [];

    public function add(Expectation $expectation): void
    {
        $this->expectations[] = $expectation;
    }

    /**
     * @param array<string, string[]> $headers
     */
    public function match(string $method, string $url, array $headers = [], mixed $body = null): ?Expectation
    {
        for ($i = count($this->expectations) - 1; $i >= 0; $i--) {
            if ($this->expectations[$i]->matcher->matches($method, $url, $headers, $body)) {
                $this->expectations[$i]->incrementMatchCount();
                return $this->expectations[$i];
            }
        }
        return null;
    }

    /** @return list<Expectation> */
    public function all(): array
    {
        return $this->expectations;
    }

    /** @return list<array{description: string, matchCount: int}> */
    public function describeAll(): array
    {
        return array_map(
            fn (Expectation $e) => ['description' => $e->matcher->describe(), 'matchCount' => $e->matchCount],
            $this->expectations,
        );
    }

    public function reset(): void
    {
        $this->expectations = [];
    }
}
