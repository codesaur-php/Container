# API Reference

This document contains the complete API description for the `codesaur/container` package.

**Language:** [Монгол](API.md) | English

---

## Table of Contents

1. [Container Class](#container-class)
2. [Methods](#methods)
   - [get()](#get)
   - [has()](#has)
   - [set()](#set)
   - [remove()](#remove)
   - [alias()](#alias)
   - [bind()](#bind)
3. [Exceptions](#exceptions)
   - [NotFoundException](#notfoundexception)
   - [ContainerException](#containerexception)
4. [Usage Examples](#usage-examples)

---

## Container Class

### Description

The `Container` class is a lightweight dependency injection container that implements the PSR-11 `ContainerInterface` standard.

This container registers services by **class name** only and uses Reflection to create instances with constructor arguments.

**Lazy Loading:** Services are created only when needed (when `get()` is called). This improves performance for heavy services.

### Namespace

```php
namespace codesaur\Container;
```

### Class Signature

```php
class Container implements ContainerInterface
```

### Properties

#### `protected array<string, mixed> $definitions`

All service definitions stored in the container. Instances are created only when needed with the help of lazy loading.

#### `protected array<string, mixed> $instances`

Cache of created instances. Once created, the instance is reused (singleton pattern).

---

## Methods

### get()

Get a service by ID name.

#### Signature

```php
public function get(string $name): mixed
```

#### Parameters

- **`string $name`** - Class or service ID

#### Returns

- **`mixed`** - Registered service instance

#### Throws

- **`NotFoundException`** - If service is not found

#### Description

Lazy loading: Service is created only on the first call. Subsequent calls return the cached instance.

#### Example

```php
use codesaur\Container\Container;

$container = new Container();
$container->set(MyService::class);

// Instance is created on first call
$service = $container->get(MyService::class);

// Subsequent calls return cached instance
$service2 = $container->get(MyService::class); // $service === $service2
```

---

### has()

Check if a service with the given name is registered.

#### Signature

```php
public function has(string $name): bool
```

#### Parameters

- **`string $name`** - Service name to check

#### Returns

- **`bool`** - `true` if registered, `false` otherwise

#### Description

PSR-11 standard `ContainerInterface::has()` method. Checks if a service is registered.

#### Example

```php
$container = new Container();

// Not registered
$container->has(MyService::class); // false

// Register
$container->set(MyService::class);

// Registered
$container->has(MyService::class); // true
```

---

### set()

Register a new service in the container.

#### Signature

```php
public function set(string $name, mixed $definition = []): void
```

#### Parameters

- **`string $name`** - Class name or service ID to register
- **`mixed $definition`** - Constructor arguments for creating class (array) or callable Closure

#### Returns

- **`void`**

#### Throws

- **`NotFoundException`** - If class does not exist
- **`ContainerException`** - On duplicate registration

#### Description

Lazy loading: Service is not created now, only the definition is stored. Instance is created when `get()` is first called.

**Notes:**
- `$name` parameter must be a **class name** (for callable, any string is allowed)
- Throws `NotFoundException` if class does not exist
- Duplicate registration is prohibited
- Uses ReflectionClass to create instance with constructor arguments (when `get()` is called)

#### Example 1: Register Class Without Parameters

```php
$container = new Container();
$container->set(MyService::class);
```

#### Example 2: Register Class With Parameters

```php
$container->set(MyService::class, ['arg1', 123, true]);
```

#### Example 3: Register Using Callable/Closure

```php
$container->set('config', function() {
    return [
        'db_host' => 'localhost',
        'db_name' => 'mydb',
    ];
});

// Using Container inside
$container->set('logger', function(Container $c) {
    $config = $c->get('config');
    return new Logger($config['db_host']);
});
```

---

### remove()

Remove a service from the container.

#### Signature

```php
public function remove(string $name): void
```

#### Parameters

- **`string $name`** - Service name to remove

#### Returns

- **`void`**

#### Description

Removes both the definition and cached instance.

#### Example

```php
$container = new Container();
$container->set(MyService::class);
$container->get(MyService::class);

// Remove service
$container->remove(MyService::class);

// Can register again
$container->set(MyService::class);
```

---

### alias()

Assign an alias name to a service.

#### Signature

```php
public function alias(string $alias, string $name): void
```

#### Parameters

- **`string $alias`** - Alias name
- **`string $name`** - Real service name

#### Returns

- **`void`**

#### Throws

- **`NotFoundException`** - If service is not found
- **`ContainerException`** - If duplicate alias or alias name equals service name

#### Description

Aliases allow accessing one service by multiple names. All aliases return the same instance (singleton behavior).

**Notes:**
- Service must be registered before creating an alias
- Duplicate aliases are not allowed
- Alias name cannot be the same as the service name
- Works together with interface binding

#### Example

```php
$container = new Container();
$container->set(Logger::class);

// Create aliases
$container->alias('log', Logger::class);
$container->alias('app.logger', Logger::class);

// All names return the same instance
$logger1 = $container->get(Logger::class);
$logger2 = $container->get('log');
$logger3 = $container->get('app.logger');

// $logger1 === $logger2 === $logger3
```

#### Example: Interface Binding with Alias

```php
interface LoggerInterface {
    public function log(string $message): void;
}

class FileLogger implements LoggerInterface {
    public function log(string $message): void {}
}

$container = new Container();
$container->bind(LoggerInterface::class, FileLogger::class);
$container->set(FileLogger::class, ['/var/log/app.log']);

// Create alias for interface
$container->alias('logger', LoggerInterface::class);

$logger1 = $container->get(LoggerInterface::class);
$logger2 = $container->get('logger');

// $logger1 === $logger2
```

---

### bind()

Bind an interface to an implementation.

#### Signature

```php
public function bind(string $interface, string $implementation): void
```

#### Parameters

- **`string $interface`** - Interface name
- **`string $implementation`** - Implementation class name

#### Returns

- **`void`**

#### Throws

- **`NotFoundException`** - If class does not exist
- **`ContainerException`** - If class does not implement interface or duplicate binding

#### Description

Binds an interface to an implementation. When getting the interface, the implementation instance is returned.

**Notes:**
- The implementation class must implement the interface
- Duplicate bindings are not allowed
- Works together with auto-wiring

#### Example

```php
interface LoggerInterface {
    public function log(string $message): void;
}

class FileLogger implements LoggerInterface {
    private string $filePath;
    
    public function __construct(string $filePath) {
        $this->filePath = $filePath;
    }
    
    public function log(string $message): void {
        file_put_contents($this->filePath, $message . PHP_EOL, FILE_APPEND);
    }
}

$container = new Container();

// Bind interface to implementation
$container->bind(LoggerInterface::class, FileLogger::class);
$container->set(FileLogger::class, ['/var/log/app.log']);

// Getting interface returns implementation instance
$logger = $container->get(LoggerInterface::class);
$logger->log('Test message'); // Uses FileLogger instance
```

---

## Exceptions

### NotFoundException

Exception thrown when the requested service or entry is not found in the container.

#### Class Signature

```php
class NotFoundException extends Exception implements NotFoundExceptionInterface
```

#### Usage Cases

- When calling `get($name)` and service with that name is not registered
- When calling `set()` and class does not exist
- When the ID to get from container is wrong or does not exist

#### Example

```php
use codesaur\Container\Container;
use codesaur\Container\NotFoundException;

$container = new Container();

try {
    $service = $container->get('NonExistentService');
} catch (NotFoundException $e) {
    echo $e->getMessage(); // "Entry not found: NonExistentService"
}
```

---

### ContainerException

Exception representing errors that can occur during container operation.

#### Class Signature

```php
class ContainerException extends Exception implements ContainerExceptionInterface
```

#### Usage Cases

- When registering a service with duplicate name
- When errors occur from Reflection while creating object
- When any internal logic in Container fails

#### Example

```php
use codesaur\Container\Container;
use codesaur\Container\ContainerException;

$container = new Container();
$container->set(MyService::class);

try {
    // Attempt duplicate registration
    $container->set(MyService::class);
} catch (ContainerException $e) {
    echo $e->getMessage(); // "Container already contains entry named [MyService]"
}
```

---

## Usage Examples

### Example 1: Simple Usage

```php
use codesaur\Container\Container;

$container = new Container();

// Register class
$container->set(Printer::class, ['Hello, World!']);

// Get service
$printer = $container->get(Printer::class);
$printer->print(); // "Hello, World!"
```

### Example 2: Dependency Injection

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

// Register Database
$container->set(Database::class, ['localhost']);

// Register UserService (pass Database)
$container->set('user_service', function(Container $c) {
    $db = $c->get(Database::class);
    return new UserService($db);
});

$userService = $container->get('user_service');
```

### Example 3: Configuration Service

```php
$container = new Container();

// Register Configuration
$container->set('config', function() {
    return [
        'app_name' => 'My App',
        'debug' => true,
        'database' => [
            'host' => 'localhost',
            'name' => 'mydb',
        ],
    ];
});

// Use Configuration
$config = $container->get('config');
echo $config['app_name']; // "My App"
```

### Example 4: Service Replacement

```php
$container = new Container();

// Initial service
$container->set('service', function() {
    return new Service('initial');
});

$service1 = $container->get('service');

// Replace service
$container->remove('service');
$container->set('service', function() {
    return new Service('replaced');
});

$service2 = $container->get('service');
// $service1 !== $service2
```

### Example 5: Singleton Pattern

```php
$container = new Container();
$container->set(HeavyService::class);

// First call - instance is created
$service1 = $container->get(HeavyService::class);

// Subsequent calls return cached instance
$service2 = $container->get(HeavyService::class);

// $service1 === $service2 (same instance)
```

### Example 6: Interface Binding

```php
interface LoggerInterface {
    public function log(string $message): void;
}

class FileLogger implements LoggerInterface {
    private string $filePath;
    
    public function __construct(string $filePath) {
        $this->filePath = $filePath;
    }
    
    public function log(string $message): void {
        file_put_contents($this->filePath, $message . PHP_EOL, FILE_APPEND);
    }
}

$container = new Container();

// Bind interface to implementation
$container->bind(LoggerInterface::class, FileLogger::class);
$container->set(FileLogger::class, ['/var/log/app.log']);

// Getting interface returns implementation instance
$logger = $container->get(LoggerInterface::class);
$logger->log('Test message'); // Uses FileLogger instance
```

### Example 7: Interface Binding with Auto-wiring

```php
class UserService {
    private LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }
    
    public function getLogger(): LoggerInterface {
        return $this->logger;
    }
}

$container = new Container();

// Interface binding
$container->bind(LoggerInterface::class, FileLogger::class);
$container->set(FileLogger::class, ['/var/log/app.log']);

// Auto-wiring: FileLogger is automatically injected into UserService constructor
$container->set(UserService::class);

$service = $container->get(UserService::class);
$service->getLogger()->log('User action'); // Uses FileLogger instance
```

### Example 8: Service Aliases

```php
$container = new Container();
$container->set(Logger::class);

// Create multiple aliases
$container->alias('log', Logger::class);
$container->alias('app.logger', Logger::class);
$container->alias('logger_service', Logger::class);

// All names return the same instance
$logger1 = $container->get(Logger::class);
$logger2 = $container->get('log');
$logger3 = $container->get('app.logger');
$logger4 = $container->get('logger_service');

// $logger1 === $logger2 === $logger3 === $logger4
```

### Example 9: Service Alias with Interface Binding

```php
interface LoggerInterface {
    public function log(string $message): void;
}

class FileLogger implements LoggerInterface {
    private string $filePath;
    
    public function __construct(string $filePath) {
        $this->filePath = $filePath;
    }
    
    public function log(string $message): void {
        file_put_contents($this->filePath, $message . PHP_EOL, FILE_APPEND);
    }
}

$container = new Container();

// Interface binding
$container->bind(LoggerInterface::class, FileLogger::class);
$container->set(FileLogger::class, ['/var/log/app.log']);

// Create alias for interface
$container->alias('logger', LoggerInterface::class);

// All names return the same instance
$logger1 = $container->get(LoggerInterface::class);
$logger2 = $container->get('logger');

// $logger1 === $logger2
$logger2->log('Test message'); // Uses FileLogger instance
```

---

## PSR-11 Compliance

This container fully implements the PSR-11 standard:

- ✅ `ContainerInterface::get()` - Get service
- ✅ `ContainerInterface::has()` - Check if service exists
- ✅ `ContainerExceptionInterface` - General error
- ✅ `NotFoundExceptionInterface` - Not found error

---

## Auto-wiring

### Description

Auto-wiring is a mechanism that automatically resolves dependencies from the container when constructor parameters have class type hints.

### How It Works

1. When creating an instance, the container checks constructor parameters
2. For parameters with class type hints, it tries to resolve from the container
3. If a dependency is registered in the container, it is automatically injected
4. User-provided arguments take precedence over auto-wiring
5. Optional parameters use default values if dependency is not found

### Example

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

### Benefits

- ⚡ **Easy to use**: No need to manually pass dependencies
- 🎯 **Automatic**: Automatically resolves and injects from class type hints
- 🔄 **Flexible**: User-provided arguments take precedence over auto-wiring

### Notes

- Auto-wiring only works for parameters with **class type hints**
- Dependencies must be registered in the container
- Throws `ContainerException` if dependency is not found
- Optional parameters use default values if dependency is not found

---

## Best Practices

1. **Use Lazy Loading**: Create heavy services only when needed
2. **Singleton Pattern**: Reuse a single instance
3. **Exception Handling**: Use `try-catch` blocks to properly handle errors
4. **Service Naming**: Use clear, understandable names
5. **Configuration Management**: Register configuration as callable
6. **Auto-wiring**: Use auto-wiring for automatic dependency resolution
7. **Interface Binding**: Use interface binding for loose coupling
8. **Service Aliases**: Use aliases for accessing services by multiple names

---

## See Also

- [README](README.EN.md) - General introduction, installation, usage
- [CODE_REVIEW](CODE_REVIEW.EN.md) - Code review report
- [CHANGELOG](CHANGELOG.EN.md) - Changelog
