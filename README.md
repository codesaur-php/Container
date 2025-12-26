# codesaur/container

![CI](https://github.com/codesaur-php/Container/workflows/CI/badge.svg)
[![PHP Version](https://img.shields.io/badge/php-%5E8.2.1-777BB4.svg?logo=php)](https://www.php.net/)
![License](https://img.shields.io/badge/License-MIT-green.svg)

Хөнгөн, хурдан, PSR-11 стандартад нийцсэн **dependency injection container**.  
Энэ багц нь codesaur framework-ийн үндсэн бүрэлдэхүүн боловч ямар ч PHP төслөөс бие даан ашиглах боломжтой.

🌐 **Homepage:** https://codesaur.net  
📦 **Source:** https://github.com/codesaur-php/Container  
🏢 **Organization:** https://github.com/codesaur-php

---

### Онцлогууд

- ✔ PSR-11 `ContainerInterface`-ийг хэрэгжүүлдэг  
- ✔ Lazy Loading - Сервисүүд зөвхөн шаардлагатай үед үүсгэгдэнэ  
- ✔ Auto-wiring - Автоматаар dependency resolve хийх  
- ✔ Interface Binding - Interface-үүдийг implementation-уудтай холбох  
- ✔ Service Aliases - Нэг сервисийг олон нэрээр авах  
- ✔ Reflection ашиглан автоматаар instance үүсгэнэ  
- ✔ Closure / callable дэмжлэг  
- ✔ Framework-agnostic - Бүх PHP framework-тэй нийцтэй  
- ✔ Ямар ч гадны нэмэлт хамааралгүй

### Суурилуулалт

```bash
composer require codesaur/container
```

**Шаардлага:** PHP 8.2.1+

### Хурдан эхлэл

```php
use codesaur\Container\Container;

$container = new Container();

// Сервис бүртгэх
$container->set(MyService::class);

// Сервис авах
$service = $container->get(MyService::class);
```

### Дэлгэрэнгүй мэдээлэл

- 📖 [Бүрэн танилцуулга](docs/mn/README.md) - Суурилуулалт, хэрэглээ, жишээнүүд
- 📚 [API тайлбар](docs/mn/api.md) - Бүх метод, exception-үүдийн тайлбар
- 🔍 [Код шалгалт](docs/mn/code-review.md) - Код шалгалтын тайлан
- 📝 [Changelog](docs/mn/changelog.md) - Өөрчлөлтийн түүх

---

## English

Lightweight, fast, PSR-11 compliant **dependency injection container**.  
This package is a component of the codesaur framework but can be used independently in any PHP project.

### Features

- ✔ Implements PSR-11 `ContainerInterface`  
- ✔ Lazy Loading - Services are created only when needed  
- ✔ Auto-wiring - Automatic dependency resolution  
- ✔ Interface Binding - Bind interfaces to implementations  
- ✔ Service Aliases - Access one service by multiple names  
- ✔ Automatically creates instances from classes using Reflection  
- ✔ Closure / callable support  
- ✔ Framework-agnostic - Compatible with all PHP frameworks  
- ✔ No external dependencies required

### Installation

```bash
composer require codesaur/container
```

**Requirements:** PHP 8.2.1+

### Quick Start

```php
use codesaur\Container\Container;

$container = new Container();

// Register service
$container->set(MyService::class);

// Get service
$service = $container->get(MyService::class);
```

### Documentation

- 📖 [Full Documentation](docs/en/README.md) - Installation, usage, examples
- 📚 [API Reference](docs/en/api.md) - Complete API documentation
- 🔍 [Code Review](docs/en/code-review.md) - Code review report
- 📝 [Changelog](docs/en/changelog.md) - Version history

---

## License

This project is licensed under the MIT License.

## Author

**Narankhuu**  
📧 codesaur@gmail.com  
📲 [+976 99000287](https://wa.me/97699000287)  
🌐 https://github.com/codesaur
