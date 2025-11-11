# Contributing to Go Goroutines PECL Extension

Thank you for your interest in contributing to the Go Goroutines PECL Extension! This document provides guidelines and instructions for contributing.

## Code of Conduct

By participating in this project, you agree to maintain a respectful and inclusive environment for all contributors.

## How to Contribute

### Reporting Bugs

If you find a bug, please create an issue with:

- **Clear title and description**
- **Steps to reproduce** the bug
- **Expected behavior** vs actual behavior
- **Environment details**: PHP version, Go version, OS
- **Code samples** if applicable

### Suggesting Features

Feature suggestions are welcome! Please:

1. Check if the feature has already been requested
2. Describe the use case and benefits
3. Consider backwards compatibility
4. Be open to discussion and feedback

### Contributing Code

#### Getting Started

1. **Fork the repository**
   ```bash
   git clone https://github.com/mucan54/php_go_goroutines.git
   cd php_go_goroutines
   ```

2. **Create a feature branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Set up development environment**
   ```bash
   ./build.sh
   ```

#### Development Guidelines

**Code Style**

- **Go Code**: Follow standard Go conventions (`gofmt`)
- **C Code**: Use consistent indentation (tabs), K&R style braces
- **PHP Code**: Follow PSR-12 coding standard

**Go Code Best Practices**
```go
// Export functions with proper error handling
//export FunctionName
func FunctionName(param C.int) C.int {
    defer func() {
        if r := recover(); r != nil {
            // Handle panics
        }
    }()
    // Function implementation
}
```

**C Code Best Practices**
```c
// Properly handle PHP parameters
PHP_FUNCTION(function_name) {
    zend_long param;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "l", &param) == FAILURE) {
        RETURN_FALSE;
    }

    // Implementation
}
```

#### Testing

1. **Add tests** for new functionality
   ```bash
   # Create test in tests/
   cp tests/basic_test.php tests/your_feature_test.php
   ```

2. **Run all tests**
   ```bash
   make test
   ```

3. **Test manually**
   ```bash
   php -d extension=modules/go_goroutines.so your_test.php
   ```

#### Documentation

- Update README.md for new features
- Add inline comments for complex code
- Update API reference section
- Include usage examples

#### Commit Messages

Write clear, concise commit messages:

```
feat: Add goroutine cancellation support

- Implement CancelGoroutine() function
- Add PHP function go_cancel()
- Update tests and documentation
```

**Format:**
- `feat:` New feature
- `fix:` Bug fix
- `docs:` Documentation changes
- `test:` Test additions/changes
- `refactor:` Code refactoring
- `perf:` Performance improvements
- `chore:` Build process, dependencies

#### Pull Request Process

1. **Ensure all tests pass**
   ```bash
   make clean
   make build
   make test
   ```

2. **Update documentation**
   - README.md for user-facing changes
   - Code comments for implementation details
   - CHANGELOG.md with your changes

3. **Create pull request**
   - Clear title describing the change
   - Description of what changed and why
   - Reference any related issues
   - Include test results

4. **Code review**
   - Address review comments
   - Keep discussion professional and constructive
   - Be patient - reviews take time

5. **Merge**
   - Squash commits if requested
   - Ensure CI passes
   - Maintainer will merge when ready

### Development Setup

#### Required Tools

- Go 1.18+
- PHP 7.4+ with development headers
- GCC or Clang
- Git
- Make

#### Building

```bash
# Full build
make build

# Just Go library
make go-build

# Clean build artifacts
make clean

# Install locally
sudo make install
```

#### Debugging

**Go Code**
```go
import "log"

func DebugFunction() {
    log.Printf("Debug info: %v", someValue)
}
```

**C Code**
```c
#include <stdio.h>

PHP_FUNCTION(debug_function) {
    fprintf(stderr, "Debug info\n");
}
```

**PHP Code**
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Project Structure

```
php_go_goroutines/
├── go_src/              # Go source code
│   ├── goroutines.go    # Main Go implementation
│   └── go.mod           # Go module definition
├── tests/               # PHP test scripts
├── examples/            # Example usage scripts
├── php_go_goroutines.c  # C extension code
├── php_go_goroutines.h  # C header file
├── config.m4            # Build configuration
├── Makefile             # Build system
├── build.sh             # Build script
├── install.sh           # Installation script
└── README.md            # Documentation
```

### Areas for Contribution

#### High Priority

- [ ] Goroutine cancellation mechanism
- [ ] Better error handling and reporting
- [ ] Performance benchmarks
- [ ] Windows support
- [ ] Thread pool management

#### Medium Priority

- [ ] Streaming results with channels
- [ ] Binary data support
- [ ] Custom timeout per goroutine
- [ ] Integration examples (frameworks)
- [ ] Performance profiling tools

#### Low Priority

- [ ] Web-based monitoring UI
- [ ] Prometheus metrics export
- [ ] Docker examples
- [ ] Kubernetes deployment guide

### Getting Help

- **Documentation**: Check README.md and code comments
- **Issues**: Search existing issues
- **Discussions**: Use GitHub Discussions for questions
- **Email**: Contact maintainers for sensitive issues

### Recognition

Contributors will be:
- Listed in CONTRIBUTORS.md
- Mentioned in release notes
- Credited in relevant documentation

Thank you for contributing! 🎉
