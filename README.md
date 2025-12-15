# codesaur/container
Хөнгөн, хурдан, PSR-11 стандартад нийцсэн **dependency injection container**.  
Энэ багц нь codesaur framework-ийн үндсэн бүрэлдэхүүн боловч ямар ч PHP төслөөс бие даан ашиглах боломжтой.

---

## Агуулга

1. [Танилцуулга](#танилцуулга)
2. [Суурилуулалт](#суурилуулалт)
3. [Хэрэглээ](#хэрэглээ)
4. [API Reference](#api-reference)
5. [Advanced Usage](#advanced-usage)
6. [Example хавтас](#example-хавтас)
7. [Тест ажиллуулах](#тест-ажиллуулах)
8. [Код шалгалт](#код-шалгалт)
9. [Лиценз](#лиценз)
10. [Зохиогч](#зохиогч)

---

## Танилцуулга

`codesaur/container` нь PHP 8.2+ орчинд ажиллах **dependency injection container** бөгөөд:

- ✔ PSR-11 `ContainerInterface`-ийг хэрэгжүүлдэг  
- ✔ Lazy Loading - Сервисүүд зөвхөн шаардлагатай үед (get() дуудагдах үед) үүсгэгдэнэ  
- ✔ Reflection ашиглан классуудаас автоматаар instance үүсгэнэ  
- ✔ Closure / callable ашиглан services бүртгэх боломжтой  
- ✔ Standalone скрипт болон бүх төрлийн PHP төсөлд ашиглахад тохиромжтой  
- ✔ Framework-agnostic тул codesaur, Laravel, Symfony, Slim болон бусад бүх PHP framework-тэй бүрэн нийцтэй  
- ✔ Ямар ч гадны нэмэлт хамааралгүй

---

## Суурилуулалт

Composer ашиглан суулгана:

```bash
composer require codesaur/container
```

Шаардлага:

- PHP **8.2.1+**
- Composer
- Гадны ямар ч dependency шаардлагагүй

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

### `set(string $name, array $args = []): void`
- Класс бүртгэх
- Lazy Loading: Instance одоо үүсгэгдэхгүй, зөвхөн тодорхойлолт хадгалагдана
- Reflection ашиглан instance үүсгэнэ (get() дуудагдах үед)
- Давхар бүртгэхийг хориглоно

---

### `get(string $name): mixed`
- Бүртгэлтэй instance буцаана
- Lazy Loading: Эхний удаа дуудахад instance үүсгэнэ, дараа нь кэшлэгдсэн instance буцаана
- Байхгүй бол `NotFoundException` шиднэ

---

### `has(string $name): bool`
- Бүртгэлтэй эсэхийг шалгана

---

### `remove(string $name): void`
- Сервисийг контейнерээс устгана

---

### Exceptions

#### `NotFoundException`
- Бүртгэлгүй service авахыг оролдох үед

#### `ContainerException`
- Давхар бүртгэх  
- Reflection-иас алдаа гарах  
- Бусад дотоод алдаанууд

---

## Lazy Loading

Энэ контейнер нь **lazy loading** механизмыг дэмждэг. Энэ нь:

### Давуу талууд

- ⚡ **Гүйцэтгэл**: Хүнд сервисүүд зөвхөн шаардлагатай үед үүсгэгдэнэ
- 💾 **Санах ой**: Ашиглаагүй сервисүүд санах ой эзлэхгүй
- 🎯 **Оновчтой ашиглалт**: Зөвхөн ашиглаж буй сервисүүд л үүсгэгдэнэ

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

### Бусад сервисээс хамаарал авах

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

### Simple aliasing

```php
$container->set(Logger::class);
$container->set('log', [ $container->get(Logger::class) ]);
```
---

## Example хавтас

`example/index.php` файлд контейнерийн бодит жишээ бий:

Локал серверээр ажиллуулах:

```bash
php -S localhost:9080 -t example
```

---

## Тест ажиллуулах

Энэ төсөлд PHPUnit ашиглан unit test-үүд бий. Тестүүдийг ажиллуулахын тулд:

### 1. Composer dependencies суулгах

```bash
composer install
```

Энэ нь PHPUnit болон бусад dev dependencies-ийг суулгана.

### 2. Тест ажиллуулах

Бүх тестүүдийг ажиллуулах:

```bash
vendor/bin/phpunit
```

Эсвэл тодорхой тест файл ажиллуулах:

```bash
vendor/bin/phpunit tests/ContainerTest.php
```

### 3. Тест coverage харах

Код coverage-ийг харах:

```bash
vendor/bin/phpunit --coverage-text
```

### Тестүүдийн бүтэц

- `tests/ContainerTest.php` - Container классын unit test-үүд
- `tests/ContainerExceptionTest.php` - ContainerException классын test-үүд
- `tests/NotFoundExceptionTest.php` - NotFoundException классын test-үүд

Тестүүд нь дараах зүйлсийг шалгана:
- ✅ Service бүртгэх, авах үйлдлүүд
- ✅ Constructor аргументууд дамжуулах
- ✅ Exception handling
- ✅ Callable/closure дэмжлэг
- ✅ Lazy loading (сервис зөвхөн get() дуудагдах үед үүсгэгдэх)
- ✅ Instance кэшлэлт (singleton behavior)
- ✅ PSR-11 стандартын нийцтэй байдал
- ✅ Edge case-үүд (optional parameters, no constructor, гэх мэт)

---

## Код шалгалт

Төслийн кодын нарийвчилсан шалгалтын тайланг [CODE_REVIEW.md](CODE_REVIEW.md) файлаас харна уу.

---

## Лиценз

Энэ төсөл MIT лицензтэй.

---

## Зохиогч

**Narankhuu**  
📧 codesaur@gmail.com  
📱 +976 99000287  
🌐 https://github.com/codesaur  
