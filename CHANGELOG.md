# Changelog

All notable changes to the Go Goroutines PECL Extension will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned Features
- Goroutine cancellation support
- Streaming results via channels
- Custom error types
- Performance profiling tools
- Windows build support

## [0.1.0] - 2025-11-11

### Added
- Initial release of Go Goroutines PECL Extension
- Core goroutine management functionality
- Basic PHP API with 8 functions:
  - `go_start_goroutine()` - Start simple goroutine
  - `go_start_goroutine_with_task()` - Start with task description
  - `go_start_delayed()` - Start with delay
  - `go_check_status()` - Check goroutine status
  - `go_get_result()` - Get goroutine result
  - `go_wait()` - Wait for completion with timeout
  - `go_cleanup()` - Cleanup resources
  - `go_get_stats()` - Get statistics
- Thread-safe goroutine result storage
- Automatic memory management between Go and PHP
- Comprehensive test suite
- Example scripts demonstrating usage patterns
- Build system with Makefile and shell scripts
- Complete documentation (README, QUICKSTART, CONTRIBUTING)
- Support for PHP 7.4+ and Go 1.18+

### Features
- Cgo bridge between PHP and Go runtime
- Concurrent goroutine execution
- Status monitoring and result retrieval
- Timeout support for waiting operations
- Real-time statistics tracking
- Panic recovery and error handling
- Unique ID generation for goroutines
- Mutex-protected shared state

### Documentation
- Comprehensive README with API reference
- Quick start guide
- Contributing guidelines
- Example applications
- Installation instructions
- Troubleshooting guide

### Build System
- Automated build scripts (build.sh, install.sh)
- Makefile with multiple targets
- PECL-compatible config.m4
- Go module support
- Cross-platform compatibility (Linux, macOS)

### Testing
- Basic functionality tests
- Concurrent execution tests
- Demo application
- Error handling tests

### Known Limitations
- Cannot pass PHP callbacks to goroutines directly
- Results must be string-serializable
- No goroutine cancellation yet
- Limited to Linux and macOS (Windows support planned)

## [0.0.1] - Development

### Added
- Project structure
- Initial Cgo experiments
- Proof of concept implementation

---

## Version History

### Version Numbering

- **MAJOR**: Incompatible API changes
- **MINOR**: New functionality, backwards compatible
- **PATCH**: Bug fixes, backwards compatible

### Support Policy

- Latest version receives full support
- Previous minor version receives security updates
- Older versions are unsupported

---

For upgrade instructions and migration guides, see [UPGRADING.md](UPGRADING.md) (when available).

[Unreleased]: https://github.com/mucan54/php_go_goroutines/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/mucan54/php_go_goroutines/releases/tag/v0.1.0
[0.0.1]: https://github.com/mucan54/php_go_goroutines/commits/main
