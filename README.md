# codesaur/container

![CI](https://github.com/codesaur-php/Container/workflows/CI/badge.svg)
[![PHP Version](https://img.shields.io/badge/php-%5E8.2.1-777BB4.svg?logo=php)](https://www.php.net/)
![License](https://img.shields.io/badge/License-MIT-green.svg)

## Агуулга / Table of Contents

1. [Монгол](#1-монгол-тайлбар) | 2. [English](#2-english-description) | 3. [Getting Started](#3-getting-started)

---

## 1. Монгол тайлбар

Хөнгөн, хурдан, PSR-11 стандартад нийцсэн **dependency injection container**.  
Энэ багц нь **codesaur ecosystem**-ийн үндсэн бүрэлдэхүүн боловч ямар ч PHP төслөөс бие даан ашиглах боломжтой.

### Онцлогууд

- PSR-11 `ContainerInterface`-ийг хэрэгжүүлдэг  
- Lazy Loading - Сервисүүд зөвхөн шаардлагатай үед үүсгэгдэнэ  
- Auto-wiring - Dependency-үүдийг автоматаар resolve хийх  
- Interface Binding - Interface-үүдийг implementation-уудтай холбох  
- Service Aliases - Нэг сервисийг олон нэрээр авах  
- Reflection ашиглан автоматаар instance үүсгэнэ  
- Closure / callable дэмжлэг  
- Framework-agnostic - Бүх PHP framework-тэй нийцтэй  
- Ямар ч гадны нэмэлт хамааралгүй

### Дэлгэрэнгүй мэдээлэл

- [Бүрэн танилцуулга](docs/mn/README.md) - Суурилуулалт, хэрэглээ, жишээнүүд
- [API тайлбар](docs/mn/api.md) - Бүх метод, exception-үүдийн тайлбар
- [Код шалгалт](docs/mn/code-review.md) - Код шалгалтын тайлан

---

## 2. English description

Lightweight, fast, PSR-11 compliant **dependency injection container**.  
This package is a component of the **codesaur ecosystem** but can be used independently in any PHP project.

### Features

- Implements PSR-11 `ContainerInterface`  
- Lazy Loading - Services are created only when needed  
- Auto-wiring - Automatic dependency resolution  
- Interface Binding - Bind interfaces to implementations  
- Service Aliases - Access one service by multiple names  
- Automatically creates instances from classes using Reflection  
- Closure / callable support  
- Framework-agnostic - Compatible with all PHP frameworks  
- No external dependencies required

### Documentation

- [Full Documentation](docs/en/README.md) - Installation, usage, examples
- [API Reference](docs/en/api.md) - Complete API documentation
- [Code Review](docs/en/code-review.md) - Code review report

---

## 3. Getting Started

### Requirements

- PHP **8.2.1+**
- Composer

### Installation

Composer ашиглан суулгана / Install via Composer:

```bash
composer require codesaur/container
```

### Quick Example

```php
use codesaur\Container\Container;

// Контейнер үүсгэх / Create container
$container = new Container();

// Класс бүртгэх / Register class
$container->set(MyService::class);

// Параметртэй класс бүртгэх / Register class with parameters
$container->set(Printer::class, ['Hello, World!']);

// Сервис авах / Get service
$service = $container->get(MyService::class);
$printer = $container->get(Printer::class);

// Сервис байгаа эсэхийг шалгах / Check if service exists
if ($container->has(MyService::class)) {
    // ...
}

// Сервис ашиглах / Use service
$printer->print(); // Output: Hello, World!
```

### Running Tests

Тест ажиллуулах / Run tests:

```bash
# Бүх тестүүдийг ажиллуулах / Run all tests
composer test

# Coverage-тэй тест ажиллуулах / Run tests with coverage
composer test:coverage
```

---

## Changelog

- [CHANGELOG.md](CHANGELOG.md) - Full version history

## Contributing & Security

- [Contributing Guide](.github/CONTRIBUTING.md)
- [Security Policy](.github/SECURITY.md)

## License

This project is licensed under the MIT License.

## Author

**Narankhuu**  
codesaur@gmail.com  
https://github.com/codesaur

**codesaur Ecosystem:** https://codesaur.net
