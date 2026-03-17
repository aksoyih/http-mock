<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock;

use Aksoyih\HttpMock\Matching\RequestMatcherInterface;
use InvalidArgumentException;

final class Expectation
{
    public private(set) int $matchCount = 0;

    public function __construct(
        public readonly RequestMatcherInterface $matcher,
        public readonly int $status = 200,
        public readonly array $headers = [],
        public readonly mixed $body = null,
        public readonly ?int $times = null,
        public readonly ?int $atLeast = null,
        public readonly ?int $atMost = null,
    ) {
        if ($this->times !== null && ($this->atLeast !== null || $this->atMost !== null)) {
            throw new InvalidArgumentException(
                'Cannot combine "times" with "atLeast" or "atMost".',
            );
        }

        if ($this->atLeast !== null && $this->atMost !== null && $this->atLeast > $this->atMost) {
            throw new InvalidArgumentException(
                sprintf('"atLeast" (%d) cannot be greater than "atMost" (%d).', $this->atLeast, $this->atMost),
            );
        }
    }

    public function hasConstraints(): bool
    {
        return $this->times !== null || $this->atLeast !== null || $this->atMost !== null;
    }

    public function incrementMatchCount(): void
    {
        $this->matchCount++;
    }
}
