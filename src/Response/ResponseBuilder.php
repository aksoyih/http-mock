<?php

declare(strict_types=1);

namespace Aksoyih\HttpMock\Response;

use Aksoyih\HttpMock\Expectation;
use Aksoyih\HttpMock\Matching\RequestMatcherInterface;
use Aksoyih\HttpMock\MockRegistry;

final class ResponseBuilder
{
    private mixed $body = null;
    private int $status = 200;
    private array $headers = [];
    private ?int $times = null;
    private ?int $atLeast = null;
    private ?int $atMost = null;

    public function __construct(
        private readonly MockRegistry $registry,
        private readonly RequestMatcherInterface $matcher,
    ) {}

    public function willReturn(mixed $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function withStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function withHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    public function expect(?int $times = null, ?int $atLeast = null, ?int $atMost = null): self
    {
        $this->times = $times;
        $this->atLeast = $atLeast;
        $this->atMost = $atMost;
        return $this;
    }

    public function register(): void
    {
        $this->registry->add(new Expectation(
            matcher: $this->matcher,
            status: $this->status,
            headers: $this->headers,
            body: $this->body,
            times: $this->times,
            atLeast: $this->atLeast,
            atMost: $this->atMost,
        ));
    }
}
