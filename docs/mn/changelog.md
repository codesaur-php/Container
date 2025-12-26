# Changelog

Энэ файлд `codesaur/container` багцын бүх чухал өөрчлөлтүүдийг тэмдэглэнэ.

**Хэл:** Монгол | [English](../en/changelog.md)

Формат нь [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) стандартыг дагаж,
энэ төсөл [Semantic Versioning](https://semver.org/spec/v2.0.0.html) стандартыг баримтална.

---

## [3.1.0] - 2025-12-26

### Added
- ✅ Auto-wiring функц нэмэгдсэн
  - Constructor-ын параметрүүдэд class type hint байвал container-ээс автоматаар dependency resolve хийгдэнэ
  - User аргумент өгсөн бол түүнийг ашиглана (auto-wiring-ээс давуу)
  - Optional параметрүүдэд default value ашиглана
- ✅ Interface binding функц нэмэгдсэн
  - Interface-үүдийг implementation-уудтай холбох боломж (`bind()` метод)
  - Interface-ийг get() дуудахад implementation instance буцаана
  - Auto-wiring-тэй хамт ажиллана
- ✅ Service aliases дэмжлэг нэмэгдсэн
  - Нэг сервисийг олон нэрээр авах боломж (`alias()` метод)
  - Бүх alias-үүд ижил instance буцаана (singleton behavior)
  - Interface binding-тэй хамт ажиллана
- ✅ Auto-wiring тестүүд нэмэгдсэн
- ✅ Interface binding тестүүд нэмэгдсэн
- ✅ Service aliases тестүүд нэмэгдсэн
- ✅ README.md болон API.md-д auto-wiring, interface binding, service aliases тайлбар нэмэгдсэн
- ✅ README.EN.md болон API.EN.md-д auto-wiring, interface binding, service aliases тайлбар нэмэгдсэн
- ✅ CODE_REVIEW.md болон CODE_REVIEW.EN.md-д хэрэгжүүлсэн feature-үүдийн мэдээлэл нэмэгдсэн

### Changed
- ✅ Бүх *.md файлууд refactor хийгдсэн (нэгдсэн хэв маяг)
- ✅ Markdown syntax алдаанууд засагдсан

---

## [3.0.1] - 2025-12-25

### Added
- ✅ Англи хэл дээрх баримт бичгүүд нэмэгдсэн
  - `README.EN.md` - Ерөнхий танилцуулга, суурилуулалт, хэрэглээ
  - `API.EN.md` - API reference
  - `CODE_REVIEW.EN.md` - Код шалгалтын тайлан
  - `CHANGELOG.EN.md` - Өөрчлөлтийн түүх
- ✅ Хоёр хэлний файлууд хоорондоо холбогдсон

### Changed
- Баримт бичгүүд сайжруулагдсан

---

## [3.0.0] - 2025-12-18

### Added
- ✅ PSR-11 `ContainerInterface` стандартыг бүрэн хэрэгжүүлсэн
- ✅ Lazy Loading механизм - Сервисүүд зөвхөн шаардлагатай үед үүсгэгдэнэ
- ✅ Reflection ашиглан автоматаар instance үүсгэх
- ✅ Closure / callable дэмжлэг
- ✅ Singleton pattern (кэшлэгдсэн instance)
- ✅ `set()`, `get()`, `has()`, `remove()` методүүд
- ✅ `NotFoundException` болон `ContainerException` exception классууд
- ✅ Unit test болон Integration test-үүд
- ✅ CI/CD pipeline (GitHub Actions)
- ✅ PHP 8.2, 8.3, 8.4 дэмжлэг
- ✅ Multi-platform дэмжлэг (Ubuntu, Windows)

### Changed
- Тогтвортой хувилбар

---

## Version History

- **3.1.0** - Auto-wiring, Interface binding, Service aliases feature-үүд нэмэгдсэн
- **3.0.1** - Баримт бичгийн сайжруулалт (Англи хэл)
- **3.0.0** - Тогтвортой хувилбар

---

## Links

- [README](README.md) - Ерөнхий танилцуулга
- [API](api.md) - API бүрэн тайлбар
- [CODE_REVIEW](code-review.md) - Код шалгалтын тайлан

[3.1.0]: https://github.com/codesaur-php/Container/compare/v3.0.1...v3.1.0
[3.0.1]: https://github.com/codesaur-php/Container/compare/v3.0.0...v3.0.1
[3.0.0]: https://github.com/codesaur-php/Container/compare/v1.0...v3.0.0
