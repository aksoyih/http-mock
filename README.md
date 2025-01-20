# HTTP Mock

A PHP library for mocking HTTP clients with customizable responses and behaviors. Perfect for testing API integrations and HTTP client implementations.

## Installation

```bash
composer require aksoyih/http-mock --dev
```

## Usage

### Basic Usage

```php
use Aksoyih\HttpMock\HttpMock;

$mock = new HttpMock();

// Define a mock response
$mock->when('GET', 'https://api.example.com/users')
    ->willReturn(['id' => 1, 'name' => 'John Doe']);

// Get the mock response
$response = $mock->getMockResponse('GET', 'https://api.example.com/users');
// Returns: ['id' => 1, 'name' => 'John Doe']
```

### Customizing Response

```php
// Set status code and headers
$mock->when('POST', 'https://api.example.com/users')
    ->withStatus(201)
    ->withHeaders(['Content-Type' => 'application/json'])
    ->willReturn(['message' => 'User created']);
```

### Simulating Delays

```php
// Add a global delay to all responses
$mock->withDelay(1000); // 1 second delay

// Or reset the delay
$mock->reset();
```

### Simulating Errors

```php
// Simulate a global error
$mock->withGlobalError('Network connection failed');

// Or simulate specific endpoint errors
$mock->when('GET', 'https://api.example.com/error')
    ->withStatus(500)
    ->willReturn(['error' => 'Internal Server Error']);
```

## Features

- Mock HTTP responses for specific endpoints
- Customize response status codes and headers
- Simulate network delays
- Simulate error conditions
- Fluent interface for easy configuration
- Reset functionality for test isolation

## Requirements

- PHP 8.1 or higher

## License

MIT License 