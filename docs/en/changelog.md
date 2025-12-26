# Changelog

This file documents all notable changes to the `codesaur/container` package.

**Language:** [Монгол](../mn/changelog.md) | English

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [3.1.0] - 2025-12-26

### Added
- ✅ Auto-wiring feature added
  - Automatically resolves dependencies from container when constructor parameters have class type hints
  - User-provided arguments take priority over auto-wiring
  - Uses default values for optional parameters
- ✅ Interface binding feature added
  - Ability to bind interfaces to implementations (`bind()` method)
  - Getting interface returns implementation instance
  - Works together with auto-wiring
- ✅ Service aliases support added
  - Ability to access one service by multiple names (`alias()` method)
  - All aliases return the same instance (singleton behavior)
  - Works together with interface binding
- ✅ Auto-wiring tests added
- ✅ Interface binding tests added
- ✅ Service aliases tests added
- ✅ Documentation for auto-wiring, interface binding, and service aliases added to README.md and API.md
- ✅ Documentation for auto-wiring, interface binding, and service aliases added to README.EN.md and API.EN.md
- ✅ Implemented features information added to CODE_REVIEW.md and CODE_REVIEW.EN.md

### Changed
- ✅ All *.md files refactored (unified style)
- ✅ Markdown syntax errors fixed

---

## [3.0.1] - 2025-12-25

### Added
- ✅ English documentation added
  - `README.EN.md` - General introduction, installation, usage
  - `API.EN.md` - API reference
  - `CODE_REVIEW.EN.md` - Code review report
  - `CHANGELOG.EN.md` - Changelog
- ✅ Language links between Mongolian and English files

### Changed
- Documentation improvements

---

## [3.0.0] - 2025-12-18

### Added
- ✅ Full PSR-11 `ContainerInterface` standard implementation
- ✅ Lazy Loading mechanism - Services are created only when needed
- ✅ Automatic instance creation using Reflection
- ✅ Closure / callable support
- ✅ Singleton pattern (cached instances)
- ✅ `set()`, `get()`, `has()`, `remove()` methods
- ✅ `NotFoundException` and `ContainerException` exception classes
- ✅ Unit tests and Integration tests
- ✅ CI/CD pipeline (GitHub Actions)
- ✅ PHP 8.2, 8.3, 8.4 support
- ✅ Multi-platform support (Ubuntu, Windows)

### Changed
- Stable release

---

## Version History

- **3.1.0** - Auto-wiring, Interface binding, Service aliases features added
- **3.0.1** - Documentation improvements (English language)
- **3.0.0** - Stable release

---

## Links

- [README](README.md) - General introduction
- [API](api.md) - API reference
- [CODE_REVIEW](code-review.md) - Code review

[3.1.0]: https://github.com/codesaur-php/Container/compare/v3.0.1...v3.1.0
[3.0.1]: https://github.com/codesaur-php/Container/compare/v3.0.0...v3.0.1
[3.0.0]: https://github.com/codesaur-php/Container/compare/v1.0...v3.0.0
