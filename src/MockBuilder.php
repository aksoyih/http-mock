<?php

namespace Aksoyih\HttpMock;

class MockBuilder
{
    private array $response = [
        'status' => 200,
        'headers' => [],
        'body' => null
    ];

    public function __construct(
        private HttpMock $httpMock,
        private string $method,
        private string $url
    ) {}

    public function willReturn(mixed $body): self
    {
        $this->response['body'] = $body;
        return $this;
    }

    public function withStatus(int $status): self
    {
        $this->response['status'] = $status;
        return $this;
    }

    public function withHeaders(array $headers): self
    {
        $this->response['headers'] = array_merge($this->response['headers'], $headers);
        return $this;
    }

    public function __destruct()
    {
        $this->httpMock->addMockResponse($this->method, $this->url, $this->response);
    }
} 