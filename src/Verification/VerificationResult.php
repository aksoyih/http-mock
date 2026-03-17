<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Verification;

use Aksoyih\HttpMock\Expectation;

final class VerificationResult
{
    /**
     * @param list<string> $failures
     */
    public function __construct(
        public private(set) bool $passed,
        public private(set) array $failures = [],
    ) {
    }

    public static function check(Expectation $expectation, int $actualCount): self
    {
        if (!$expectation->hasConstraints()) {
            return new self(passed: true);
        }

        $failures = [];
        $description = $expectation->matcher->describe();

        if ($expectation->times !== null && $actualCount !== $expectation->times) {
            $failures[] = sprintf('%s: expected exactly %d call(s), got %d', $description, $expectation->times, $actualCount);
        }

        if ($expectation->atLeast !== null && $actualCount < $expectation->atLeast) {
            $failures[] = sprintf('%s: expected at least %d call(s), got %d', $description, $expectation->atLeast, $actualCount);
        }

        if ($expectation->atMost !== null && $actualCount > $expectation->atMost) {
            $failures[] = sprintf('%s: expected at most %d call(s), got %d', $description, $expectation->atMost, $actualCount);
        }

        return new self(passed: $failures === [], failures: $failures);
    }
}
