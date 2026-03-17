<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Exception;

use RuntimeException;

final class UnmatchedRequestException extends RuntimeException
{
    /**
     * @param list<array{description: string, matchCount: int}> $definedMocks
     */
    public static function forRequest(string $method, string $url, array $definedMocks = []): self
    {
        $message = sprintf('No mock defined for %s %s.', $method, $url);

        if ($definedMocks !== []) {
            $message .= "\nDefined mocks:";
            foreach ($definedMocks as $mock) {
                $message .= sprintf("\n  - %s (matched %d times)", $mock['description'], $mock['matchCount']);
            }
        }

        return new self($message);
    }
}
