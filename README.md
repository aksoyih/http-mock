# HTTP Mock

[![CI](https://github.com/aksoyih/http-mock/actions/workflows/ci.yml/badge.svg)](https://github.com/aksoyih/http-mock/actions/workflows/ci.yml)

A PHP library for mocking HTTP clients with customizable responses, flexible request matching, and full verification support. Works with PSR-18, Guzzle, and Symfony HttpClient.

## Installation

```bash
composer require aksoyih/http-mock --dev
```

## Quick Start

```php
use Aksoyih\HttpMock\HttpMock;

// Define a mock
HttpMock::when('GET', '/users')
    ->willReturn(['id' => 1, 'name' => 'John'])
    ->withStatus(200)
    ->register();

// Create a PSR-18 client
$client = HttpMock::createClient();
$response = $client->sendRequest($request);

// Verify it was called
HttpMock::assertCalled('GET', '/users');
```

## Request Matching

### Exact Match

```php
HttpMock::when('GET', '/users/123')
    ->willReturn(['id' => 123])
    ->register();
```

### Wildcard Patterns

```php
// * matches a single path segment
HttpMock::when('GET', '/users/*/posts')
    ->willReturn([])
    ->register();

// ** matches multiple segments
HttpMock::when('GET', '/api/**')
    ->willReturn([])
    ->register();
```

### Regex Patterns

```php
HttpMock::when('GET', '#^/users/\d+$#')
    ->willReturn(['found' => true])
    ->register();
```

### Callable Matchers

```php
HttpMock::whenMatching(fn($method, $url, $headers, $body) =>
    $method === 'POST' && str_contains($url, '/admin')
)->willReturn(['access' => 'granted'])
    ->register();
```

## Shorthand

```php
HttpMock::fake([
    'GET /users/*' => HttpMock::response(['id' => 1], 200),
    'POST /users'  => HttpMock::response(['created' => true], 201),
]);
```

## Verification

```php
HttpMock::assertCalled('GET', '/users');
HttpMock::assertCalledTimes('POST', '/users', 3);
HttpMock::assertNotCalled('DELETE', '/users');

// Or use expect() constraints
HttpMock::when('GET', '/users')
    ->willReturn([])
    ->expect(times: 2)
    ->register();

// ... make requests ...

HttpMock::verify(); // throws if expectations not met
```

## HTTP Client Adapters

### PSR-18

```php
$client = HttpMock::createClient();
```

### Guzzle

```php
$handler = HttpMock::createGuzzleHandler();
$stack = HandlerStack::create($handler);
$client = new \GuzzleHttp\Client(['handler' => $stack]);
```

### Symfony HttpClient

```php
$client = HttpMock::createSymfonyClient();
```

## Unmatched Request Handling

```php
// Strict mode (default) — throws on unmatched requests
HttpMock::onUnmatched(HttpMock::THROW);

// Return 404 for unmatched requests
HttpMock::onUnmatched(HttpMock::DEFAULT_404);

// Custom handler
HttpMock::onUnmatched(fn($method, $url) => [
    'status' => 503,
    'headers' => [],
    'body' => 'Service unavailable',
]);
```

## Test Isolation

### PHPUnit Trait (recommended)

```php
use Aksoyih\HttpMock\Testing\MocksHttp;

class MyTest extends TestCase
{
    use MocksHttp; // auto-resets after each test
}
```

### Scoped

```php
HttpMock::scoped(function () {
    HttpMock::when('GET', '/users')->willReturn([])->register();
    // ... test code ...
}); // auto-resets, even on exception
```

### Manual

```php
HttpMock::reset();
```

## Requirements

- PHP 8.4+
- `psr/http-client` ^1.0
- `nyholm/psr7` ^1.0

Optional:
- `guzzlehttp/guzzle` ^7.0 (for Guzzle adapter)
- `symfony/http-client` ^7.0 (for Symfony adapter)

## License

MIT
