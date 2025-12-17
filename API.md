# API Reference

Энэ баримт бичиг нь `codesaur/container` багцын API-ийн бүрэн тайлбарыг агуулна.

---

## Агуулга

1. [Container Class](#container-class)
2. [Methods](#methods)
   - [get()](#get)
   - [has()](#has)
   - [set()](#set)
   - [remove()](#remove)
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

**Анхаарах зүйлс:**
- `$name` параметр нь заавал **класс нэр** байх ёстой (callable-ийн хувьд аль ч string байж болно)
- Класс байхгүй бол `NotFoundException` шиднэ
- Давхар бүртгэхийг хориглоно
- ReflectionClass ашиглаж constructor-ын аргументуудаар instance үүсгэнэ (`get()` дуудагдах үед)

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

Тодорхойлолт болон кэшлэгдсэн instance-ийг хоёуланг нь устгана.

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

### Жишээ 2: Dependency Injection

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

// UserService бүртгэх (Database-ийг дамжуулах)
$container->set('user_service', function(Container $c) {
    $db = $c->get(Database::class);
    return new UserService($db);
});

$userService = $container->get('user_service');
```

### Жишээ 3: Configuration Service

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

### Жишээ 4: Service Replacement

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

### Жишээ 5: Singleton Pattern

```php
$container = new Container();
$container->set(HeavyService::class);

// Эхний дуудлага - instance үүсгэгдэнэ
$service1 = $container->get(HeavyService::class);

// Дараагийн дуудлагуудад кэшлэгдсэн instance буцаана
$service2 = $container->get(HeavyService::class);

// $service1 === $service2 (ижил instance)
```

---

## PSR-11 Compliance

Энэ контейнер нь PSR-11 стандартыг бүрэн хэрэгжүүлдэг:

- ✅ `ContainerInterface::get()` - Сервис авах
- ✅ `ContainerInterface::has()` - Сервис байгаа эсэхийг шалгах
- ✅ `ContainerExceptionInterface` - Ерөнхий алдаа
- ✅ `NotFoundExceptionInterface` - Олдохгүй алдаа

---

## Best Practices

1. **Lazy Loading ашиглах**: Хүнд сервисүүдийг зөвхөн шаардлагатай үед үүсгэх
2. **Singleton Pattern**: Нэг instance-ийг дахин ашиглах
3. **Exception Handling**: `try-catch` блок ашиглан алдааг зохих ёсоор боловсруулах
4. **Service Naming**: Тодорхой, ойлгомжтой нэр ашиглах
5. **Configuration Management**: Configuration-ийг callable-аар бүртгэх

---

## See Also

- [README.md](README.md) - Ерөнхий танилцуулга, суурилуулалт, хэрэглээ
- [CODE_REVIEW.md](CODE_REVIEW.md) - Код шалгалтын тайлан
