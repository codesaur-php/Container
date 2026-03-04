# codesaur/container

Хөнгөн, хурдан, PSR-11 стандартад нийцсэн **dependency injection container**.  
Энэ багц нь **codesaur ecosystem**-ийн үндсэн бүрэлдэхүүн боловч ямар ч PHP төслөөс бие даан ашиглах боломжтой.

---

## Агуулга

1. [Танилцуулга](#танилцуулга)
2. [Суурилуулалт](#суурилуулалт)
3. [Хэрэглээ](#хэрэглээ)
4. [API Reference](#api-reference)
5. [Lazy Loading](#lazy-loading)
6. [Advanced Usage](#advanced-usage)
7. [Example хавтас](#example-хавтас)
8. [Тест ажиллуулах](#тест-ажиллуулах)
9. [CI/CD](#cicd)
10. [Код шалгалт](#код-шалгалт)
11. [Changelog](#changelog)
12. [Лиценз](#лиценз)
13. [Зохиогч](#зохиогч)

---

## Танилцуулга

`codesaur/container` нь PHP 8.2+ орчинд ажиллах **dependency injection container** бөгөөд:

- PSR-11 `ContainerInterface`-ийг хэрэгжүүлдэг  
- Lazy Loading - Сервисүүд зөвхөн шаардлагатай үед үүсгэгдэнэ  
- Auto-wiring - Dependency-үүдийг автоматаар resolve хийх  
- Interface Binding - Interface-үүдийг implementation-уудтай холбох  
- Service Aliases - Нэг сервисийг олон нэрээр авах  
- Reflection ашиглан автоматаар instance үүсгэнэ  
- Closure / callable дэмжлэг  
- Framework-agnostic - Бүх PHP framework-тэй нийцтэй  
- Ямар ч гадны нэмэлт хамааралгүй

---

## Суурилуулалт

Composer ашиглан суулгана:

```bash
composer require codesaur/container
```

Шаардлага:

- PHP **8.2.1+**
- Composer

---

## Хэрэглээ

### Контейнер үүсгэх

```php
use codesaur\Container\Container;

$container = new Container();
```

---

### Класс бүртгэх

```php
$container->set(MyClass::class);
```

Lazy Loading: `set()` дуудахад instance үүсгэгдэхгүй, зөвхөн тодорхойлолт хадгалагдана. Instance нь анх удаа `get()` дуудагдах үед үүсгэгдэнэ.

---

### Класс параметртэйгээр бүртгэх

```php
$container->set(MyService::class, ['hello', 123]);
```

Reflection автоматаар:

```
new MyService('hello', 123);
```

гэсэн instance үүсгэнэ.

---

### Service авах

```php
$service = $container->get(MyService::class);
```

Lazy Loading: Эхний удаа `get()` дуудахад instance үүсгэгдэнэ. Дараагийн дуудлагуудад кэшлэгдсэн instance буцаана (singleton pattern).

---

### Service байгаа эсэхийг шалгах

```php
$container->has(MyService::class); // true / false
```

---

### Service устгах

```php
$container->remove(MyService::class);
```

---

## API Reference

### Товч танилцуулга

#### `set(string $name, mixed $definition = []): void`
- Класс бүртгэх
- Lazy Loading: Instance одоо үүсгэгдэхгүй, зөвхөн тодорхойлолт хадгалагдана
- Reflection ашиглан instance үүсгэнэ (get() дуудагдах үед)
- Давхар бүртгэхийг хориглоно

#### `get(string $name): mixed`
- Бүртгэлтэй instance буцаана
- Lazy Loading: Эхний удаа дуудахад instance үүсгэнэ, дараа нь кэшлэгдсэн instance буцаана
- Байхгүй бол `NotFoundException` шиднэ

#### `has(string $name): bool`
- Бүртгэлтэй эсэхийг шалгана

#### `remove(string $name): void`
- Сервисийг контейнерээс устгана

#### Exceptions

**`NotFoundException`**
- Бүртгэлгүй service авахыг оролдох үед

**`ContainerException`**
- Давхар бүртгэх  
- Reflection-иас алдаа гарах  
- Бусад дотоод алдаанууд

Дэлгэрэнгүй мэдээллийг [API](api.md) файлаас үзнэ үү. (PHPDoc-уудаас Cursor AI ашиглан автоматаар үүсгэсэн)

---

## Lazy Loading

Энэ контейнер нь **lazy loading** механизмыг дэмждэг. Энэ нь:

### Давуу талууд

- **Гүйцэтгэл**: Хүнд сервисүүд зөвхөн шаардлагатай үед үүсгэгдэнэ
- **Санах ой**: Ашиглаагүй сервисүүд санах ой эзлэхгүй
- **Оновчтой ашиглалт**: Зөвхөн ашиглаж буй сервисүүд л үүсгэгдэнэ

### Хэрхэн ажилладаг

```php
// set() дуудахад instance үүсгэгдэхгүй
$container->set(HeavyService::class);

// get() дуудахад л instance үүсгэгдэнэ
$service = $container->get(HeavyService::class);

// Дараагийн дуудлагуудад кэшлэгдсэн instance буцаана
$service2 = $container->get(HeavyService::class); // $service === $service2
```

### Callable-тай ашиглах

```php
// Callable ч мөн lazy loading-тэй ажиллана
$container->set('config', function() {
    // Энэ код зөвхөн get() дуудагдах үед ажиллана
    return [
        'db_host' => 'localhost',
        'db_name' => 'mydb',
    ];
});

// Callable одоо дуудагдахгүй
// ...

// get() дуудахад л callable ажиллана
$config = $container->get('config');
```

---

## Advanced Usage

### Auto-wiring (Автомат Dependency Resolution)

Container нь **auto-wiring** механизмыг дэмждэг. Энэ нь constructor-ын параметрүүдэд class type hint байвал container-ээс автоматаар dependency-г resolve хийх боломжийг олгодог.

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

// Зөвхөн dependency-үүдийг бүртгэх
$container->set(Database::class, ['localhost']);
$container->set(UserService::class);

// Auto-wiring: UserService-ийн constructor-т Database байгаа тул автоматаар inject хийгдэнэ
$userService = $container->get(UserService::class);
// UserService-ийн constructor-т Database автоматаар дамжигдсэн байна
```

**Auto-wiring-ийн давуу талууд:**
- **Хялбар ашиглалт**: Dependency-үүдийг гараар дамжуулах шаардлагагүй
- **Автомат**: Constructor-ын class type hint-ээс автоматаар олдож inject хийгдэнэ
- **Уян хатан**: Хэрэв user аргумент өгсөн бол түүнийг ашиглана (auto-wiring-ээс давуу)

**Анхаарах зүйлс:**
- Auto-wiring нь зөвхөн **class type hint**-тэй параметрүүдэд ажиллана
- Container-т бүртгэгдсэн dependency байх ёстой
- Хэрэв dependency олдохгүй бол `ContainerException` шиднэ
- Optional параметрүүдэд default value ашиглана (dependency олдохгүй бол)

### Бусад сервисээс хамаарал авах (Гараар)

Хэрэв auto-wiring ашиглахгүй бол dependency-г гараар дамжуулж болно:

```php
class A {}
class B {
    public function __construct(A $a) {}
}

$container->set(A::class);
$container->set(B::class, [$container->get(A::class)]); // Гараар дамжуулах

$b = $container->get(B::class);
```

---

### Closure / callable ашиглан service бүртгэх

Container нь callable / closure-ийг дэмждэг.  
Энэ тохиолдолд сервисийг *factory function* хэлбэрээр бүртгэнэ.

```php
$container->set('config', fn() => [
    'db_host' => 'localhost',
    'debug'   => true,
]);
```

Container дотор ашиглах жишээ:

```php
$container->set(Logger::class, function ($c) {
    $cfg = $c->get('config');
    return new Logger($cfg['db_host'], $cfg['debug']);
});
```

Service дуудах:

```php
$logger = $container->get(Logger::class);
```

Энэ хэлбэр нь:
- Хөнгөн **factory pattern**  
- Дотоод хамааралтай сервисүүдийг container-аас авах боломжтой  
- Runtime үед динамик утга хийхэд тохиромжтой  

---

### Runtime үед service солих

```php
$container->remove(Database::class);
$container->set(Database::class, ['127.0.0.1']);
```

---

### Dynamic arguments

```php
$container->set(Printer::class, ['Hello world!']);
```

---

### Service Aliases

Container нь **service aliases** механизмыг дэмждэг. Энэ нь нэг сервисийг олон нэрээр авах боломжийг олгодог.

```php
$container->set(Logger::class);
$container->alias('log', Logger::class);
$container->alias('app.logger', Logger::class);

// Бүх нэрээр ижил instance буцаана
$logger1 = $container->get(Logger::class);
$logger2 = $container->get('log');
$logger3 = $container->get('app.logger');

// $logger1 === $logger2 === $logger3 (ижил instance)
```

**Alias-ийн давуу талууд:**
- **Олон нэр**: Нэг сервисийг олон нэрээр авах боломжтой
- **Singleton**: Бүх alias-үүд ижил instance буцаана
- **Interface binding**: Interface binding-тэй хамт ажиллана
- **Хялбар**: `alias()` метод ашиглан хялбар бүртгэх

**Анхаарах зүйлс:**
- Alias үүсгэхээсээ өмнө сервис бүртгэгдсэн байх ёстой
- Давхар alias хийхийг хориглоно
- Alias нэр нь бодит сервисийн нэртэй ижил байх ёсгүй

---

### Interface Binding

Interface-үүдийг implementation-уудтай холбох боломжтой. Энэ нь dependency injection-д interface ашиглах боломжийг олгодог.

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

// Interface-ийг implementation-тай холбох
$container->bind(LoggerInterface::class, FileLogger::class);
$container->set(FileLogger::class, ['/var/log/app.log']);

// Interface-ээр авахад implementation instance буцаана
$logger = $container->get(LoggerInterface::class);
// $logger нь FileLogger instance байна

// Auto-wiring-тэй хамт ашиглах
class UserService {
    public function __construct(LoggerInterface $logger) {}
}

$container->set(UserService::class);
$service = $container->get(UserService::class);
// UserService-ийн constructor-т FileLogger автоматаар inject хийгдэнэ
```

**Interface Binding-ийн давуу талууд:**
- **Loose Coupling**: Interface ашиглаж implementation-аас хамааралгүй болно
- **Уян хатан**: Implementation-ийг хялбар солих боломжтой
- **Auto-wiring**: Auto-wiring-тэй хамт ажиллана

---

## Example хавтас

`example/index.php` файлд контейнерийн бодит жишээ бий:

Локал серверээр ажиллуулах:

```bash
php -S localhost:9080 -t example
```

---

## Тест ажиллуулах

Энэ төсөлд PHPUnit ашиглан unit test болон integration test-үүд бий.

### Dependencies суулгах

```bash
composer install
```

### Тест ажиллуулах

#### Composer Script ашиглах

```bash
composer test              # Бүх тестүүдийг ажиллуулах
composer test:coverage     # Coverage-тэй тест ажиллуулах
```

#### PHPUnit шууд ашиглах

```bash
vendor/bin/phpunit                                    # Бүх тестүүдийг ажиллуулах
vendor/bin/phpunit tests/ContainerTest.php          # Тодорхой тест файл ажиллуулах
vendor/bin/phpunit tests/IntegrationTest.php         # Integration test ажиллуулах
vendor/bin/phpunit --coverage-text                   # Тест coverage харах
vendor/bin/phpunit --filter testSetAndGet tests/ContainerTest.php  # Тодорхой method ажиллуулах
```

**Windows хэрэглэгчид:** `vendor/bin/phpunit`-ийг `vendor\bin\phpunit.bat` гэж солино

### Тестүүдийн бүтэц

- `tests/ContainerTest.php` - Container классын unit test-үүд
- `tests/ContainerExceptionTest.php` - ContainerException классын test-үүд
- `tests/NotFoundExceptionTest.php` - NotFoundException классын test-үүд
- `tests/IntegrationTest.php` - Integration test-үүд (бодит хэрэглээний сценариуд)

### Тестүүд юу шалгадаг

- Service бүртгэх, авах үйлдлүүд
- Constructor аргументууд дамжуулах
- Exception handling
- Callable/closure дэмжлэг
- Lazy loading (сервис зөвхөн get() дуудагдах үед үүсгэгдэх)
- Instance кэшлэлт (singleton behavior)
- PSR-11 стандартын нийцтэй байдал
- Edge case-үүд (optional parameters, no constructor, гэх мэт)
- Integration test-үүд (бодит application сценариуд, dependency chain, service replacement, гэх мэт)

---

## CI/CD

Энэ төсөлд GitHub Actions ашиглан CI/CD pipeline тохируулсан байна.

### CI Pipeline

GitHub Actions workflow нь дараах зүйлсийг гүйцэтгэнэ:

- **Multi-version PHP тест**: PHP 8.2, 8.3, 8.4 дээр тест ажиллуулна
- **Multi-platform тест**: Ubuntu болон Windows дээр тест ажиллуулна
- **Code coverage**: Codecov руу coverage тайлан илгээнэ
- **Syntax check**: PHP файлуудын синтакс шалгалт

### CI Status

CI pipeline нь дараах үйлдлүүдэд автоматаар ажиллана:
- `main`, `master`, `develop` branch-ууд руу push хийхэд
- Pull request үүсгэхэд

CI статусыг GitHub repository-ийн Actions tab-аас харж болно.

### Локал дээр CI-тэй ижил тест ажиллуулах

CI дээр ажиллаж буй тестүүдийг локал дээр ажиллуулахын тулд дээрх [Тест ажиллуулах](#тест-ажиллуулах) хэсэгт байгаа командуудыг ашиглана уу.

---

## Код шалгалт

Төслийн кодын нарийвчилсан шалгалтын тайланг [CODE_REVIEW](code-review.md) файлаас харна уу. (Cursor AI ашиглан үүсгэсэн)

---

## Changelog

Багцын бүх өөрчлөлтийн түүхийг [CHANGELOG](../../CHANGELOG.md) файлаас үзнэ үү.

---

## Лиценз

Энэ төсөл MIT лицензтэй.

---

## Зохиогч

**Narankhuu**  
https://github.com/codesaur  
