# Quick Start Guide

Get up and running with Go Goroutines PECL Extension in 5 minutes!

## Installation

### Prerequisites

```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install -y php-dev php-pear build-essential golang

# CentOS/RHEL
sudo yum install -y php-devel php-pear gcc make golang

# macOS
brew install php go
```

### Build and Install

```bash
# Clone repository
git clone https://github.com/yourusername/php_go_goroutines.git
cd php_go_goroutines

# Build (automated)
./build.sh

# Install (requires sudo)
sudo ./install.sh
```

Or use Make:

```bash
make build
sudo make install
```

### Verify

```bash
php -m | grep go_goroutines
# Output: go_goroutines
```

## Basic Usage

### Example 1: Hello Goroutine

```php
<?php
// Start a goroutine
$id = go_start_goroutine();

// Wait for completion
if (go_wait($id, 5000)) {
    echo go_get_result($id) . "\n";
    go_cleanup($id);
}
```

### Example 2: With Task Description

```php
<?php
// Start with task name
$id = go_start_goroutine_with_task("Process user data");

// Wait and get result
if (go_wait($id, 5000)) {
    $result = go_get_result($id);
    echo "Result: $result\n";
    go_cleanup($id);
}
```

### Example 3: Multiple Concurrent Tasks

```php
<?php
// Start multiple goroutines
$tasks = [];
$tasks[] = go_start_goroutine_with_task("Fetch users");
$tasks[] = go_start_goroutine_with_task("Fetch products");
$tasks[] = go_start_goroutine_with_task("Fetch orders");

// Wait for all
foreach ($tasks as $id) {
    if (go_wait($id, 10000)) {
        echo go_get_result($id) . "\n";
        go_cleanup($id);
    }
}
```

### Example 4: Delayed Execution

```php
<?php
// Start task that completes after 1 second
$id = go_start_delayed(1000);

echo "Task started, doing other work...\n";
sleep(1);

if (go_check_status($id) === 1) {
    echo go_get_result($id) . "\n";
    go_cleanup($id);
}
```

### Example 5: Monitoring

```php
<?php
// Start some tasks
for ($i = 0; $i < 5; $i++) {
    go_start_delayed(rand(100, 500));
}

// Monitor status
echo go_get_stats() . "\n";
// Output: Total: 5, Active: 3, Completed: 2, Failed: 0, Go Routines: 8
```

## API Quick Reference

| Function | Description | Returns |
|----------|-------------|---------|
| `go_start_goroutine()` | Start simple goroutine | int (ID) |
| `go_start_goroutine_with_task(string)` | Start with task name | int (ID) |
| `go_start_delayed(int $ms)` | Start with delay | int (ID) |
| `go_check_status(int $id)` | Check status | int (-1/0/1) |
| `go_get_result(int $id)` | Get result | string |
| `go_wait(int $id, int $timeout_ms)` | Wait for completion | bool |
| `go_cleanup(int $id)` | Cleanup resources | void |
| `go_get_stats()` | Get statistics | string |

## Running Examples

```bash
# Basic test
php -d extension=go_goroutines.so tests/basic_test.php

# Concurrent test
php -d extension=go_goroutines.so tests/concurrent_test.php

# Full demo
php -d extension=go_goroutines.so examples/demo.php
```

## Common Patterns

### Pattern 1: Fire and Forget

```php
<?php
$id = go_start_goroutine();
// Don't wait, cleanup later
go_cleanup($id);
```

### Pattern 2: Wait All

```php
<?php
$ids = [/* ... goroutine IDs ... */];

foreach ($ids as $id) {
    go_wait($id, 10000);
    $result = go_get_result($id);
    // Process result
    go_cleanup($id);
}
```

### Pattern 3: Wait Any (First Completed)

```php
<?php
$ids = [/* ... goroutine IDs ... */];
$completed = null;

while ($completed === null) {
    foreach ($ids as $id) {
        if (go_check_status($id) === 1) {
            $completed = $id;
            break;
        }
    }
    usleep(10000); // Sleep 10ms
}

echo "First completed: " . go_get_result($completed) . "\n";
```

### Pattern 4: Timeout Handling

```php
<?php
$id = go_start_goroutine();

if (go_wait($id, 1000)) {
    // Success
    echo go_get_result($id) . "\n";
} else {
    // Timeout
    echo "Task timed out!\n";
}

go_cleanup($id);
```

## Troubleshooting

### Extension not loading?

```bash
# Check if file exists
ls -l $(php-config --extension-dir)/go_goroutines.so

# Check PHP configuration
php --ini

# Enable manually
php -d extension=go_goroutines.so -m | grep go_goroutines
```

### Build errors?

```bash
# Clean and rebuild
make clean
make build

# Check prerequisites
go version    # Should be 1.18+
php --version # Should be 7.4+
phpize --version
gcc --version
```

### Runtime errors?

```bash
# Check error log
tail -f /var/log/php/error.log

# Run with error reporting
php -d error_reporting=E_ALL -d display_errors=1 your_script.php
```

## Next Steps

- Read the [full README](README.md) for detailed documentation
- Check [examples](examples/) for more use cases
- Review [API reference](README.md#api-reference) for all functions
- See [CONTRIBUTING](CONTRIBUTING.md) to help improve the project

## Getting Help

- **Issues**: [GitHub Issues](https://github.com/yourusername/php_go_goroutines/issues)
- **Discussions**: [GitHub Discussions](https://github.com/yourusername/php_go_goroutines/discussions)
- **Documentation**: [README](README.md)

Happy coding with goroutines in PHP! 🚀
