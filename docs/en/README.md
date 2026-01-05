# codesaur/container

Lightweight, fast, PSR-11 compliant **dependency injection container**.  
This package is a component of the **codesaur ecosystem** but can be used independently in any PHP project.

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
- ✔ Lazy Loading - Services are created only when needed  
- ✔ Auto-wiring - Automatic dependency resolution  
- ✔ Interface Binding - Bind interfaces to implementations  
- ✔ Service Aliases - Access one service by multiple names  
- ✔ Automatically creates instances from classes using Reflection  
- ✔ Closure / callable support  
- ✔ Framework-agnostic - Compatible with all PHP frameworks  
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

For detailed information, see [API](api.md) file. (Auto-generated from PHPDoc using Cursor AI)

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

### Auto-wiring (Automatic Dependency Resolution)

The container supports **auto-wiring** mechanism. This allows automatic dependency resolution from the container when constructor parameters have class type hints.

```php
class Database {
    public function __construct(string $host) {
        // ...
    }
}

class UserService {
    public function __construct(Database $db) {
        // ...
    }
}

$container = new Container();

// Register only dependencies
$container->set(Database::class, ['localhost']);
$container->set(UserService::class);

// Auto-wiring: Database is automatically injected into UserService constructor
$userService = $container->get(UserService::class);
// Database is automatically passed to UserService constructor
```

**Benefits of Auto-wiring:**
- ⚡ **Easy to use**: No need to manually pass dependencies
- 🎯 **Automatic**: Automatically resolves and injects from class type hints
- 🔄 **Flexible**: User-provided arguments take precedence over auto-wiring

**Notes:**
- Auto-wiring only works for parameters with **class type hints**
- Dependencies must be registered in the container
- Throws `ContainerException` if dependency is not found
- Optional parameters use default values if dependency is not found

### Getting Dependencies from Other Services (Manual)

If you don't want to use auto-wiring, you can manually pass dependencies:

```php
class A {}
class B {
    public function __construct(A $a) {}
}

$container->set(A::class);
$container->set(B::class, [$container->get(A::class)]); // Manual passing

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

### Service Aliases

The container supports **service aliases** mechanism. This allows accessing one service by multiple names.

```php
$container->set(Logger::class);
$container->alias('log', Logger::class);
$container->alias('app.logger', Logger::class);

// All names return the same instance
$logger1 = $container->get(Logger::class);
$logger2 = $container->get('log');
$logger3 = $container->get('app.logger');

// $logger1 === $logger2 === $logger3 (same instance)
```

**Benefits of Aliases:**
- 🎯 **Multiple names**: Access one service by multiple names
- 🔄 **Singleton**: All aliases return the same instance
- ✅ **Interface binding**: Works together with interface binding
- ⚡ **Easy**: Easy to register using `alias()` method

**Notes:**
- Service must be registered before creating an alias
- Duplicate aliases are not allowed
- Alias name cannot be the same as the service name

---

### Interface Binding

Interfaces can be bound to implementations. This enables using interfaces in dependency injection.

```php
interface LoggerInterface {
    public function log(string $message): void;
}

class FileLogger implements LoggerInterface {
    public function __construct(string $filePath) {}
    public function log(string $message): void {}
}

class DatabaseLogger implements LoggerInterface {
    public function __construct(string $host) {}
    public function log(string $message): void {}
}

$container = new Container();

// Bind interface to implementation
$container->bind(LoggerInterface::class, FileLogger::class);
$container->set(FileLogger::class, ['/var/log/app.log']);

// Getting interface returns implementation instance
$logger = $container->get(LoggerInterface::class);
// $logger is a FileLogger instance

// Use with auto-wiring
class UserService {
    public function __construct(LoggerInterface $logger) {}
}

$container->set(UserService::class);
$service = $container->get(UserService::class);
// FileLogger is automatically injected into UserService constructor
```

**Benefits of Interface Binding:**
- 🎯 **Loose Coupling**: Use interfaces to avoid dependency on implementations
- 🔄 **Flexible**: Easy to swap implementations
- ✅ **Auto-wiring**: Works together with auto-wiring

---

## Example Folder

The `example/index.php` file contains real examples of the container:

Run with local server:

```bash
php -S localhost:9080 -t example
```

---

## Running Tests

This project includes unit tests and integration tests using PHPUnit.

### Install Dependencies

```bash
composer install
```

### Run Tests

#### Using Composer Scripts (Recommended)

```bash
composer test              # Run all tests
composer test:coverage     # Run tests with coverage
```

#### Using PHPUnit Directly

```bash
vendor/bin/phpunit                                    # Run all tests
vendor/bin/phpunit tests/ContainerTest.php          # Run specific test file
vendor/bin/phpunit tests/IntegrationTest.php        # Run integration test
vendor/bin/phpunit --coverage-text                   # View test coverage
vendor/bin/phpunit --filter testSetAndGet tests/ContainerTest.php  # Run specific method
```

**Windows users:** Replace `vendor/bin/phpunit` with `vendor\bin\phpunit.bat`

### Test Structure

- `tests/ContainerTest.php` - Unit tests for Container class
- `tests/ContainerExceptionTest.php` - Tests for ContainerException class
- `tests/NotFoundExceptionTest.php` - Tests for NotFoundException class
- `tests/IntegrationTest.php` - Integration tests (real-world usage scenarios)

### What Tests Verify

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

To run the same tests that run on CI locally, use the commands from the [Running Tests](#running-tests) section above.

---

## Code Review

For detailed code review report, see [CODE_REVIEW](code-review.md) file. (Generated using Cursor AI)

---

## Changelog

For version history and changes, see [CHANGELOG](changelog.md) file.

---

## License

This project is licensed under MIT.

---

## Author

**Narankhuu**  
https://github.com/codesaur
