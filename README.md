# codesaur/container
Хөнгөн, хурдан, PSR-11 стандартад нийцсэн **dependency injection container**.  
Энэ багц нь Codesaur framework-ийн үндсэн бүрэлдэхүүн боловч ямар ч PHP төслөөс бие даан ашиглах боломжтой.

---

## 📑 Агуулга

1. [Танилцуулга](#танилцуулга)
2. [Суурилуулалт](#суурилуулалт)
3. [Хэрэглээ](#хэрэглээ)
4. [Service Provider](#service-provider)
5. [API Reference](#api-reference)
6. [Advanced Usage](#advanced-usage)
7. [Example хавтас](#example-хавтас)
8. [Лиценз](#лиценз)
9. [Зохиогч](#зохиогч)

---

## Танилцуулга

`codesaur/container` нь PHP 8.2+ орчинд ажиллах **dependency injection container** бөгөөд:

- ✔ PSR-11 `ContainerInterface`-ийг хэрэгжүүлдэг  
- ✔ Reflection ашиглан классуудаас автоматаар instance үүсгэнэ  
- ✔ Closure / callable ашиглан services бүртгэх боломжтой  
- ✔ Service provider дэмждэг  
- ✔ Хөнгөн, хурдан, ямар ч гадны нэмэлт хамааралгүй  
- ✔ Codesaur framework болон standalone төслүүдэд төгс тохирно  

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

## Service Provider

Service provider ашиглан нэг файлд олон сервисийг бүртгэж болно.

```php
use codesaur\Container\ServiceProviderInterface;

class AppServiceProvider implements ServiceProviderInterface
{
    public function register($container): void
    {
        $container->set(Logger::class);
        $container->set(Database::class, ['localhost']);
    }
}
```

Бүртгэх:

```php
$container->register(new AppServiceProvider());
```

---

## API Reference

### `set(string $name, array $args = []): void`
- Класс бүртгэх
- Reflection ашиглан instance үүсгэнэ
- Давхар бүртгэхийг хориглоно

---

### `get(string $name): mixed`
- Бүртгэлтэй instance буцаана
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
php -S localhost:8000 -t example/
```

---

## Лиценз

Энэ төсөл MIT лицензтэй.

```
MIT License

Copyright (c) codesaur - Narankhuu
```

---

## Зохиогч

**Narankhuu**  
📧 codesaur@gmail.com
