<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Exception;

use RuntimeException;

final class VerificationException extends RuntimeException
{
    /**
     * @param list<string> $failures
     */
    public static function fromFailures(array $failures): self
    {
        $message = sprintf(
            "%d expectation(s) failed:\n  - %s",
            count($failures),
            implode("\n  - ", $failures),
        );

        return new self($message);
    }
}
