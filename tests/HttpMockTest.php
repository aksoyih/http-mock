<?php

namespace Aksoyih\HttpMock\Tests;

use Aksoyih\HttpMock\HttpMock;
use PHPUnit\Framework\TestCase;

class HttpMockTest extends TestCase
{
    private HttpMock $mock;

    protected function setUp(): void
    {
        $this->mock = new HttpMock();
    }

    public function testBasicMockResponse(): void
    {
        $expectedResponse = ['id' => 1, 'name' => 'John'];
        
        $this->mock->when('GET', 'https://api.example.com/user')
            ->willReturn($expectedResponse);

        $response = $this->mock->getMockResponse('GET', 'https://api.example.com/user');
        
        $this->assertEquals($expectedResponse, $response['body']);
        $this->assertEquals(200, $response['status']);
    }

    public function testCustomStatusAndHeaders(): void
    {
        $this->mock->when('POST', 'https://api.example.com/user')
            ->withStatus(201)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->willReturn(['message' => 'Created']);

        $response = $this->mock->getMockResponse('POST', 'https://api.example.com/user');
        
        $this->assertEquals(201, $response['status']);
        $this->assertEquals(
            ['Content-Type' => 'application/json'],
            $response['headers']
        );
    }

    public function testDelay(): void
    {
        $this->mock->withDelay(100);
        
        $start = microtime(true);
        $this->mock->when('GET', 'https://api.example.com/delayed')
            ->willReturn(['data' => 'delayed']);
        $this->mock->getMockResponse('GET', 'https://api.example.com/delayed');
        $end = microtime(true);
        
        $this->assertGreaterThanOrEqual(0.1, $end - $start);
    }

    public function testGlobalError(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Network error');
        
        $this->mock->withGlobalError('Network error');
        $this->mock->getMockResponse('GET', 'https://api.example.com/any');
    }

    public function testReset(): void
    {
        $this->mock->when('GET', 'https://api.example.com/test')
            ->willReturn(['data' => 'test']);
        $this->mock->withDelay(100);
        $this->mock->withGlobalError('Error');
        
        $this->mock->reset();
        
        $response = $this->mock->getMockResponse('GET', 'https://api.example.com/test');
        $this->assertNull($response);
    }
} 