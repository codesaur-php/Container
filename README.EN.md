# codesaur/container

![CI](https://github.com/codesaur-php/Container/workflows/CI/badge.svg)
[![PHP Version](https://img.shields.io/badge/php-%5E8.2.1-777BB4.svg?logo=php)](https://www.php.net/)
![License](https://img.shields.io/badge/License-MIT-green.svg)

Lightweight, fast, PSR-11 compliant **dependency injection container**.  
This package is a component of the codesaur framework but can be used independently in any PHP project.

**Language:** [Монгол (Mongolian)](README.md)

---

## Table of Contents

1. [Introduction](#introduction)
2. [Installation](#installation)
3. [Usage](#usage)
4. [API Reference](#api-reference)
5. [Lazy Loading](#lazy-loading)
6. [Advanced Usage](#advanced-usage)
7. [Example Folder](#example-folder)
8. [Running Tests](#running-tests)
9. [CI/CD](#cicd)
10. [Code Review](#code-review)
11. [Changelog](#changelog)
12. [License](#license)
13. [Author](#author)

---

## Introduction

`codesaur/container` is a **dependency injection container** that runs in PHP 8.2+ environments:

- ✔ Implements PSR-11 `ContainerInterface`  
- ✔ Lazy Loading - Services are created only when needed (when `get()` is called)  
- ✔ Automatically creates instances from classes using Reflection  
- ✔ Supports registering services using Closure / callable  
- ✔ Suitable for standalone scripts and all types of PHP projects  
- ✔ Framework-agnostic, fully compatible with codesaur, Laravel, Symfony, Slim, and all other PHP frameworks  
- ✔ No external dependencies required

---

## Installation

Install via Composer:

```bash
composer require codesaur/container
```

Requirements:

- PHP **8.2.1+**
- Composer
- No external dependencies required

---

## Usage

### Creating a Container

```php
use codesaur\Container\Container;

$container = new Container();
```

---

### Registering a Class

```php
$container->set(MyClass::class);
```

Lazy Loading: When `set()` is called, no instance is created, only the definition is stored. The instance is created when `get()` is first called.

---

### Registering a Class with Parameters

```php
$container->set(MyService::class, ['hello', 123]);
```

Reflection automatically creates:

```
new MyService('hello', 123);
```

---

### Getting a Service

```php
$service = $container->get(MyService::class);
```

Lazy Loading: The instance is created on the first `get()` call. Subsequent calls return the cached instance (singleton pattern).

---

### Checking if Service Exists

```php
$container->has(MyService::class); // true / false
```

---

### Removing a Service

```php
$container->remove(MyService::class);
```

---

## API Reference

### Quick Overview

#### `set(string $name, mixed $definition = []): void`
- Register a class
- Lazy Loading: Instance is not created now, only the definition is stored
- Creates instance using Reflection (when `get()` is called)
- Duplicate registration is prohibited

#### `get(string $name): mixed`
- Returns the registered instance
- Lazy Loading: Creates instance on first call, then returns cached instance
- Throws `NotFoundException` if not found

#### `has(string $name): bool`
- Checks if registered

#### `remove(string $name): void`
- Removes service from container

#### Exceptions

**`NotFoundException`**
- When trying to get a service that is not registered

**`ContainerException`**
- Duplicate registration  
- Errors from Reflection  
- Other internal errors

For detailed information, see [API.EN.md](API.EN.md) file. (Auto-generated from PHPDoc using Cursor AI)

**Монгол:** [API.md](API.md)

---

## Lazy Loading

This container supports **lazy loading** mechanism. This means:

### Benefits

- ⚡ **Performance**: Heavy services are created only when needed
- 💾 **Memory**: Unused services don't consume memory
- 🎯 **Optimal Usage**: Only services being used are created

### How It Works

```php
// No instance is created when set() is called
$container->set(HeavyService::class);

// Instance is created only when get() is called
$service = $container->get(HeavyService::class);

// Subsequent calls return the cached instance
$service2 = $container->get(HeavyService::class); // $service === $service2
```

### Using with Callable

```php
// Callable also works with lazy loading
$container->set('config', function() {
    // This code runs only when get() is called
    return [
        'db_host' => 'localhost',
        'db_name' => 'mydb',
    ];
});

// Callable is not called now
// ...

// Callable runs only when get() is called
$config = $container->get('config');
```

---

## Advanced Usage

### Getting Dependencies from Other Services

```php
class A {}
class B {
    public function __construct(A $a) {}
}

$container->set(A::class);
$container->set(B::class);

$b = $container->get(B::class);
```

---

### Registering Service with Closure / callable

Container supports callable / closure.  
In this case, the service is registered as a *factory function*.

```php
$container->set('config', fn() => [
    'db_host' => 'localhost',
    'debug'   => true,
]);
```

Example using Container inside:

```php
$container->set(Logger::class, function ($c) {
    $cfg = $c->get('config');
    return new Logger($cfg['db_host'], $cfg['debug']);
});
```

Calling the service:

```php
$logger = $container->get(Logger::class);
```

This form provides:
- Lightweight **factory pattern**  
- Ability to get dependent services from container  
- Suitable for dynamic values at runtime  

---

### Replacing Service at Runtime

```php
$container->remove(Database::class);
$container->set(Database::class, ['127.0.0.1']);
```

---

### Dynamic Arguments

```php
$container->set(Printer::class, ['Hello world!']);
```

---

### Simple Aliasing

```php
$container->set(Logger::class);
$container->set('log', [ $container->get(Logger::class) ]);
```

---

## Example Folder

The `example/index.php` file contains real examples of the container:

Run with local server:

```bash
php -S localhost:9080 -t example
```

---

## Running Tests

This project includes unit tests and integration tests using PHPUnit. To run tests:

### 1. Install Composer Dependencies

#### Windows (Command Prompt)

```cmd
composer install
```

#### Linux / macOS (Terminal)

```bash
composer install
```

This will install PHPUnit and other dev dependencies.

### 2. Run Tests

#### Windows (Command Prompt)

```cmd
# Run all tests
vendor\bin\phpunit

# Run specific test file
vendor\bin\phpunit tests\ContainerTest.php

# Run integration test
vendor\bin\phpunit tests\IntegrationTest.php
```

#### Linux / macOS (Terminal)

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/ContainerTest.php

# Run integration test
vendor/bin/phpunit tests/IntegrationTest.php
```

### 3. View Test Coverage

#### Windows (Command Prompt)

```cmd
vendor\bin\phpunit --coverage-text
```

#### Linux / macOS (Terminal)

```bash
vendor/bin/phpunit --coverage-text
```

### 4. Run Specific Test Method

#### Windows (Command Prompt)

```cmd
vendor\bin\phpunit --filter testSetAndGet tests\ContainerTest.php
```

#### Linux / macOS (Terminal)

```bash
vendor/bin/phpunit --filter testSetAndGet tests/ContainerTest.php
```

### Test Structure

- `tests/ContainerTest.php` - Unit tests for Container class
- `tests/ContainerExceptionTest.php` - Tests for ContainerException class
- `tests/NotFoundExceptionTest.php` - Tests for NotFoundException class
- `tests/IntegrationTest.php` - Integration tests (real-world usage scenarios)

Tests verify:
- ✅ Service registration and retrieval operations
- ✅ Constructor argument passing
- ✅ Exception handling
- ✅ Callable/closure support
- ✅ Lazy loading (service created only when `get()` is called)
- ✅ Instance caching (singleton behavior)
- ✅ PSR-11 standard compliance
- ✅ Edge cases (optional parameters, no constructor, etc.)
- ✅ Integration tests (real application scenarios, dependency chain, service replacement, etc.)

---

## CI/CD

This project has a CI/CD pipeline configured using GitHub Actions.

### CI Pipeline

GitHub Actions workflow performs:

- ✅ **Multi-version PHP testing**: Tests on PHP 8.2, 8.3, 8.4
- ✅ **Multi-platform testing**: Tests on Ubuntu and Windows
- ✅ **Code coverage**: Sends coverage report to Codecov
- ✅ **Syntax check**: PHP file syntax checking

### CI Status

CI pipeline automatically runs on:
- Pushes to `main`, `master`, `develop` branches
- Pull requests

CI status can be viewed in the GitHub repository's Actions tab.

### Running CI Tests Locally

To run the same tests that run on CI locally:

#### Windows (Command Prompt)

```cmd
vendor\bin\phpunit
vendor\bin\phpunit --coverage-text
vendor\bin\phpunit tests\IntegrationTest.php
```

#### Linux / macOS (Terminal)

```bash
# Run all tests
vendor/bin/phpunit

# Run with coverage
vendor/bin/phpunit --coverage-text

# Run specific test file
vendor/bin/phpunit tests/IntegrationTest.php
```

---

## Code Review

For detailed code review report, see [CODE_REVIEW.EN.md](CODE_REVIEW.EN.md) file. (Generated using Cursor AI)

**Монгол:** [CODE_REVIEW.md](CODE_REVIEW.md)

---

## Changelog

For version history and changes, see [CHANGELOG.EN.md](CHANGELOG.EN.md) file.

**Монгол:** [CHANGELOG.md](CHANGELOG.md)

---

## License

This project is licensed under MIT.

---

## Author

**Narankhuu**  
📧 codesaur@gmail.com  
📲 [+976 99000287](https://wa.me/97699000287)  
🌐 https://github.com/codesaur
