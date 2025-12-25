# Changelog

Энэ файлд `codesaur/container` багцын бүх чухал өөрчлөлтүүдийг тэмдэглэнэ.

**Хэл:** [English](CHANGELOG.EN.md)

Формат нь [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) стандартыг дагаж,
энэ төсөл [Semantic Versioning](https://semver.org/spec/v2.0.0.html) стандартыг баримтална.

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

## [Unreleased]

### Planned
- Auto-wiring функц (optional)
- Interface binding дэмжлэг
- Service aliases дэмжлэг

---

## Version History

- **3.0.1** - Баримт бичгийн сайжруулалт (Англи хэл)
- **3.0.0** - Тогтвортой хувилбар

---

## Links

- [README.md](README.md) - Ерөнхий танилцуулга
- [API.md](API.md) - API reference
- [CODE_REVIEW.md](CODE_REVIEW.md) - Код шалгалт

**English:**
- [README.EN.md](README.EN.md) - General introduction
- [API.EN.md](API.EN.md) - API reference
- [CODE_REVIEW.EN.md](CODE_REVIEW.EN.md) - Code review
