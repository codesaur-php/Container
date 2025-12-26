# API Reference

Энэ баримт бичиг нь `codesaur/container` багцын API-ийн бүрэн тайлбарыг агуулна.

**Хэл:** Монгол | [English](API.EN.md)

---

## Агуулга

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

### Тайлбар

`Container` класс нь хөнгөн жинтэй dependency injection контейнер бөгөөд PSR-11 `ContainerInterface` стандартыг хэрэгжүүлдэг.

Энэ контейнер нь зөвхөн **класс нэрээр** service бүртгэж, Reflection ашиглаж constructor-ын аргументаар instance үүсгэдэг.

**Lazy Loading:** Сервисүүд зөвхөн шаардлагатай үед (`get()` дуудагдах үед) үүсгэгдэнэ. Энэ нь хүнд сервисүүдийн хувьд гүйцэтгэлийг сайжруулна.

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

Контейнерт хадгалагдаж буй бүх сервисүүдийн тодорхойлолтууд. Lazy loading-ийн тусламжтайгаар зөвхөн шаардлагатай үед instance үүсгэнэ.

#### `protected array<string, mixed> $instances`

Үүсгэгдсэн instance-үүдийн кэш. Нэг удаа үүсгэсэн instance-ийг дахин ашиглана (singleton pattern).

---

## Methods

### get()

ID нэрээр сервис авах.

#### Signature

```php
public function get(string $name): mixed
```

#### Parameters

- **`string $name`** - Класс эсвэл сервисийн ID

#### Returns

- **`mixed`** - Бүртгэлтэй сервисийн instance

#### Throws

- **`NotFoundException`** - Сервис олдохгүй бол

#### Description

Lazy loading: Сервис зөвхөн эхний удаа дуудагдах үед үүсгэгдэнэ. Дараагийн дуудлагуудад кэшлэгдсэн instance буцаана.

#### Example

```php
use codesaur\Container\Container;

$container = new Container();
$container->set(MyService::class);

// Эхний удаа дуудахад instance үүсгэгдэнэ
$service = $container->get(MyService::class);

// Дараагийн дуудлагуудад кэшлэгдсэн instance буцаана
$service2 = $container->get(MyService::class); // $service === $service2
```

---

### has()

Тухайн нэртэй сервис бүртгэлтэй эсэхийг шалгах.

#### Signature

```php
public function has(string $name): bool
```

#### Parameters

- **`string $name`** - Шалгах сервисийн нэр

#### Returns

- **`bool`** - Бүртгэлтэй бол `true`, эсрэг тохиолдолд `false`

#### Description

PSR-11 стандартын `ContainerInterface::has()` метод. Сервис бүртгэгдсэн эсэхийг шалгана.

#### Example

```php
$container = new Container();

// Бүртгэгдээгүй
$container->has(MyService::class); // false

// Бүртгэх
$container->set(MyService::class);

// Бүртгэгдсэн
$container->has(MyService::class); // true
```

---

### set()

Контейнерт шинэ сервис бүртгэх.

#### Signature

```php
public function set(string $name, mixed $definition = []): void
```

#### Parameters

- **`string $name`** - Бүртгэх класс нэр эсвэл сервисийн ID
- **`mixed $definition`** - Класс үүсгэх constructor аргументууд (array) эсвэл callable Closure

#### Returns

- **`void`**

#### Throws

- **`NotFoundException`** - Класс байхгүй бол
- **`ContainerException`** - Давхар бүртгэх үед

#### Description

Lazy loading: Сервис одоо үүсгэгдэхгүй, зөвхөн тодорхойлолт хадгалагдана. Instance нь анх удаа `get()` дуудагдах үед үүсгэгдэнэ.

**Auto-wiring:** Constructor-ын параметрүүдэд class type hint байвал container-ээс автоматаар dependency resolve хийгдэнэ. Хэрэв user аргумент өгсөн бол түүнийг ашиглана (auto-wiring-ээс давуу).

**Анхаарах зүйлс:**
- `$name` параметр нь заавал **класс нэр** байх ёстой (callable-ийн хувьд аль ч string байж болно)
- Класс байхгүй бол `NotFoundException` шиднэ
- Давхар бүртгэхийг хориглоно
- ReflectionClass ашиглаж constructor-ын аргументуудаар instance үүсгэнэ (`get()` дуудагдах үед)
- Auto-wiring: Constructor-ын class type hint-тэй параметрүүдэд container-ээс автоматаар dependency inject хийгдэнэ

#### Example 1: Класс параметргүйгээр бүртгэх

```php
$container = new Container();
$container->set(MyService::class);
```

#### Example 2: Класс параметртэйгээр бүртгэх

```php
$container->set(MyService::class, ['arg1', 123, true]);
```

#### Example 3: Callable/Closure ашиглан бүртгэх

```php
$container->set('config', function() {
    return [
        'db_host' => 'localhost',
        'db_name' => 'mydb',
    ];
});

// Container-ийг дамжуулж ашиглах
$container->set('logger', function(Container $c) {
    $config = $c->get('config');
    return new Logger($config['db_host']);
});
```

---

### remove()

Контейнерээс сервис устгах.

#### Signature

```php
public function remove(string $name): void
```

#### Parameters

- **`string $name`** - Устгах сервисийн нэр

#### Returns

- **`void`**

#### Description

Тодорхойлолт болон кэшлэгдсэн instance-ийг хоёуланг нь устгана. Interface binding байвал түүнийг ч устгана.

#### Example

```php
$container = new Container();
$container->set(MyService::class);
$container->get(MyService::class);

// Сервис устгах
$container->remove(MyService::class);

// Дахин бүртгэх боломжтой
$container->set(MyService::class);
```

---

### alias()

Сервисэд alias нэр оноох.

#### Signature

```php
public function alias(string $alias, string $name): void
```

#### Parameters

- **`string $alias`** - Alias нэр
- **`string $name`** - Бодит сервисийн нэр

#### Returns

- **`void`**

#### Throws

- **`NotFoundException`** - Сервис олдохгүй бол
- **`ContainerException`** - Давхар alias хийх эсвэл alias нэр нь бодит сервисийн нэртэй ижил байх үед

#### Description

Alias нь нэг сервисийг олон нэрээр авах боломжийг олгодог. Бүх alias-үүд ижил instance буцаана (singleton behavior).

**Анхаарах зүйлс:**
- Alias үүсгэхээсээ өмнө сервис бүртгэгдсэн байх ёстой
- Давхар alias хийхийг хориглоно
- Alias нэр нь бодит сервисийн нэртэй ижил байх ёсгүй
- Interface binding-тэй хамт ажиллана

#### Example

```php
$container = new Container();
$container->set(Logger::class);

// Alias үүсгэх
$container->alias('log', Logger::class);
$container->alias('app.logger', Logger::class);

// Бүх нэрээр ижил instance буцаана
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

// Interface-д alias үүсгэх
$container->alias('logger', LoggerInterface::class);

$logger1 = $container->get(LoggerInterface::class);
$logger2 = $container->get('logger');

// $logger1 === $logger2
```

---

### bind()

Interface-ийг implementation-тай холбох.

#### Signature

```php
public function bind(string $interface, string $implementation): void
```

#### Parameters

- **`string $interface`** - Interface нэр
- **`string $implementation`** - Implementation класс нэр

#### Returns

- **`void`**

#### Throws

- **`NotFoundException`** - Interface эсвэл implementation байхгүй бол
- **`ContainerException`** - Implementation нь interface-ийг хэрэгжүүлэхгүй эсвэл давхар binding хийх үед

#### Description

Interface-ийг implementation класстай холбох. Ингэснээр interface-ийг `get()` дуудахад implementation instance буцаана. Auto-wiring-тэй хамт ажиллана.

**Анхаарах зүйлс:**
- Interface байх ёстой
- Implementation класс байх ёстой
- Implementation нь interface-ийг хэрэгжүүлж байх ёстой
- Давхар binding хийхийг хориглоно

#### Example 1: Энгийн interface binding

```php
interface LoggerInterface {
    public function log(string $message): void;
}

class FileLogger implements LoggerInterface {
    public function __construct(string $filePath) {}
    public function log(string $message): void {}
}

$container = new Container();

// Interface-ийг implementation-тай холбох
$container->bind(LoggerInterface::class, FileLogger::class);
$container->set(FileLogger::class, ['/var/log/app.log']);

// Interface-ээр авахад implementation instance буцаана
$logger = $container->get(LoggerInterface::class);
// $logger нь FileLogger instance байна
```

#### Example 2: Auto-wiring-тэй хамт ашиглах

```php
class UserService {
    public function __construct(LoggerInterface $logger) {}
}

$container->bind(LoggerInterface::class, FileLogger::class);
$container->set(FileLogger::class, ['/var/log/app.log']);
$container->set(UserService::class);

// Auto-wiring: UserService-ийн constructor-т FileLogger автоматаар inject хийгдэнэ
$service = $container->get(UserService::class);
```

#### Example 3: Implementation солих

```php
// Эхлээд FileLogger ашиглах
$container->bind(LoggerInterface::class, FileLogger::class);
$container->set(FileLogger::class, ['/var/log/app.log']);

// DatabaseLogger руу солих
$container->remove(LoggerInterface::class);
$container->bind(LoggerInterface::class, DatabaseLogger::class);
$container->set(DatabaseLogger::class, ['localhost', 'logs']);

$logger = $container->get(LoggerInterface::class);
// $logger нь одоо DatabaseLogger instance байна
```

---

## Exceptions

### NotFoundException

Контейнер дотор шаардсан service эсвэл entry олдохгүй үед шидэгддэг exception.

#### Class Signature

```php
class NotFoundException extends Exception implements NotFoundExceptionInterface
```

#### Ашиглагдах тохиолдлууд

- `get($name)` дуудах үед тухайн нэртэй service бүртгэгдээгүй бол
- `set()` хийх үед класс олдохгүй бол
- Контейнерээс авах гэж буй ID буруу эсвэл оршин байхгүй бол

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

Контейнерийн ажлын явцад гарч болох алдааг илэрхийлэх Exception.

#### Class Signature

```php
class ContainerException extends Exception implements ContainerExceptionInterface
```

#### Ашиглагдах тохиолдлууд

- Service бүртгэх үед давхардсан нэртэй байвал
- Reflection ашиглан объект үүсгэх явцад алдаа гарвал
- Container доторх аливаа дотоод логик амжилтгүй болвол

#### Example

```php
use codesaur\Container\Container;
use codesaur\Container\ContainerException;

$container = new Container();
$container->set(MyService::class);

try {
    // Давхар бүртгэх оролдлого
    $container->set(MyService::class);
} catch (ContainerException $e) {
    echo $e->getMessage(); // "Container already contains entry named [MyService]"
}
```

---

## Usage Examples

### Жишээ 1: Энгийн ашиглалт

```php
use codesaur\Container\Container;

$container = new Container();

// Класс бүртгэх
$container->set(Printer::class, ['Hello, World!']);

// Сервис авах
$printer = $container->get(Printer::class);
$printer->print(); // "Hello, World!"
```

### Жишээ 2: Auto-wiring (Автомат Dependency Injection)

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

// Database бүртгэх
$container->set(Database::class, ['localhost']);

// UserService бүртгэх (auto-wiring ашиглах)
$container->set(UserService::class);

// Auto-wiring: UserService-ийн constructor-т Database байгаа тул автоматаар inject хийгдэнэ
$userService = $container->get(UserService::class);
```

### Жишээ 3: Dependency Injection (Гараар)

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

// Database бүртгэх
$container->set(Database::class, ['localhost']);

// UserService бүртгэх (Database-ийг гараар дамжуулах)
$container->set('user_service', function(Container $c) {
    $db = $c->get(Database::class);
    return new UserService($db);
});

$userService = $container->get('user_service');
```

### Жишээ 4: Configuration Service

```php
$container = new Container();

// Configuration бүртгэх
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

// Configuration ашиглах
$config = $container->get('config');
echo $config['app_name']; // "My App"
```

### Жишээ 5: Service Replacement

```php
$container = new Container();

// Анхны сервис
$container->set('service', function() {
    return new Service('initial');
});

$service1 = $container->get('service');

// Сервис солих
$container->remove('service');
$container->set('service', function() {
    return new Service('replaced');
});

$service2 = $container->get('service');
// $service1 !== $service2
```

### Жишээ 6: Singleton Pattern

```php
$container = new Container();
$container->set(HeavyService::class);

// Эхний дуудлага - instance үүсгэгдэнэ
$service1 = $container->get(HeavyService::class);

// Дараагийн дуудлагуудад кэшлэгдсэн instance буцаана
$service2 = $container->get(HeavyService::class);

// $service1 === $service2 (ижил instance)
```

### Жишээ 7: Interface Binding

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

// Interface-ийг implementation-тай холбох
$container->bind(LoggerInterface::class, FileLogger::class);
$container->set(FileLogger::class, ['/var/log/app.log']);

// Interface-ээр авахад implementation instance буцаана
$logger = $container->get(LoggerInterface::class);
$logger->log('Test message'); // FileLogger instance ашиглана
```

### Жишээ 8: Interface Binding with Auto-wiring

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

// Auto-wiring: UserService-ийн constructor-т FileLogger автоматаар inject хийгдэнэ
$container->set(UserService::class);

$service = $container->get(UserService::class);
$service->getLogger()->log('User action'); // FileLogger instance ашиглана
```

### Жишээ 9: Service Aliases

```php
$container = new Container();
$container->set(Logger::class);

// Олон alias үүсгэх
$container->alias('log', Logger::class);
$container->alias('app.logger', Logger::class);
$container->alias('logger_service', Logger::class);

// Бүх нэрээр ижил instance буцаана
$logger1 = $container->get(Logger::class);
$logger2 = $container->get('log');
$logger3 = $container->get('app.logger');
$logger4 = $container->get('logger_service');

// $logger1 === $logger2 === $logger3 === $logger4
```

### Жишээ 10: Service Alias with Interface Binding

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

// Interface-д alias үүсгэх
$container->alias('logger', LoggerInterface::class);

// Бүх нэрээр ижил instance буцаана
$logger1 = $container->get(LoggerInterface::class);
$logger2 = $container->get('logger');

// $logger1 === $logger2
$logger2->log('Test message'); // FileLogger instance ашиглана
```

---

## PSR-11 Compliance

Энэ контейнер нь PSR-11 стандартыг бүрэн хэрэгжүүлдэг:

- ✅ `ContainerInterface::get()` - Сервис авах
- ✅ `ContainerInterface::has()` - Сервис байгаа эсэхийг шалгах
- ✅ `ContainerExceptionInterface` - Ерөнхий алдаа
- ✅ `NotFoundExceptionInterface` - Олдохгүй алдаа

---

## Auto-wiring

### Тайлбар

Auto-wiring нь constructor-ын параметрүүдэд class type hint байвал container-ээс автоматаар dependency resolve хийх механизм юм.

### Хэрхэн ажилладаг

1. Constructor-ын параметрүүдийг шалгана
2. Хэрэв class type hint байвал container-ээс хайна
3. Container-т бүртгэгдсэн байвал автоматаар inject хийгдэнэ
4. Хэрэв user аргумент өгсөн бол түүнийг ашиглана (auto-wiring-ээс давуу)
5. Optional параметрүүдэд default value ашиглана (dependency олдохгүй бол)

### Жишээ

```php
class Database {
    public function __construct(string $host) {}
}

class UserService {
    public function __construct(Database $db) {}
}

$container = new Container();
$container->set(Database::class, ['localhost']);
$container->set(UserService::class);

// Auto-wiring: Database автоматаар inject хийгдэнэ
$userService = $container->get(UserService::class);
```

### Хязгаарлалтууд

- Зөвхөн **class type hint**-тэй параметрүүдэд ажиллана
- Container-т бүртгэгдсэн dependency байх ёстой
- Built-in type (string, int, bool, гэх мэт) дээр ажиллахгүй
- Хэрэв dependency олдохгүй бол `ContainerException` шиднэ

---

## Interface Binding

### Тайлбар

Interface Binding нь interface-үүдийг implementation-уудтай холбох механизм юм. Энэ нь dependency injection-д interface ашиглах боломжийг олгодог.

### Хэрхэн ажилладаг

1. `bind()` метод ашиглан interface-ийг implementation-тай холбоно
2. Interface-ийг `get()` дуудахад implementation instance буцаана
3. Auto-wiring-тэй хамт ажиллана - constructor-ын interface type hint-тэй параметрүүдэд implementation автоматаар inject хийгдэнэ

### Жишээ

```php
interface LoggerInterface {
    public function log(string $message): void;
}

class FileLogger implements LoggerInterface {
    public function __construct(string $filePath) {}
    public function log(string $message): void {}
}

$container = new Container();
$container->bind(LoggerInterface::class, FileLogger::class);
$container->set(FileLogger::class, ['/var/log/app.log']);

// Interface-ээр авахад implementation instance буцаана
$logger = $container->get(LoggerInterface::class);
```

### Давуу Талууд

- 🎯 **Loose Coupling**: Interface ашиглаж implementation-аас хамааралгүй болно
- 🔄 **Уян хатан**: Implementation-ийг хялбар солих боломжтой
- ✅ **Auto-wiring**: Auto-wiring-тэй хамт ажиллана

### Хязгаарлалтууд

- Interface байх ёстой
- Implementation класс байх ёстой
- Implementation нь interface-ийг хэрэгжүүлж байх ёстой
- Давхар binding хийхийг хориглоно

---

## Best Practices

1. **Interface Binding ашиглах**: Interface ашиглаж loose coupling хийх
2. **Auto-wiring ашиглах**: Constructor dependency-үүдийг автоматаар resolve хийх
3. **Lazy Loading ашиглах**: Хүнд сервисүүдийг зөвхөн шаардлагатай үед үүсгэх
4. **Singleton Pattern**: Нэг instance-ийг дахин ашиглах
5. **Exception Handling**: `try-catch` блок ашиглан алдааг зохих ёсоор боловсруулах
6. **Service Naming**: Тодорхой, ойлгомжтой нэр ашиглах
7. **Configuration Management**: Configuration-ийг callable-аар бүртгэх

---

## See Also

- [README](README.md) - Ерөнхий танилцуулга, суурилуулалт, хэрэглээ
- [CODE_REVIEW](CODE_REVIEW.md) - Код шалгалтын тайлан
- [CHANGELOG](CHANGELOG.md) - Өөрчлөлтийн түүх
