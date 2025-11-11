# Go Goroutines PECL Extension for PHP

A high-performance PHP extension that brings Go's lightweight concurrency model to PHP through goroutines. Execute PHP tasks concurrently using Go's powerful runtime for improved I/O-bound and parallel processing performance.

## Features

- **Spawn Go Goroutines from PHP**: Create and manage lightweight concurrent tasks
- **High Performance**: Leverage Go's efficient goroutine scheduler and runtime
- **Synchronization Primitives**: Wait for goroutines, check status, and retrieve results
- **Thread-Safe**: Proper synchronization between PHP and Go runtimes
- **Easy to Use**: Simple PHP API for complex concurrency patterns
- **Resource Management**: Built-in cleanup and statistics tracking

## Architecture

This extension uses **Cgo** to bridge PHP's Zend Engine with Go's runtime, allowing PHP to:
- Start goroutines that execute independently
- Monitor goroutine execution status
- Retrieve results from completed goroutines
- Manage goroutine lifecycle and cleanup

```
┌─────────────┐
│     PHP     │
│  (Zend)     │
└──────┬──────┘
       │
       │ C Bridge (Cgo)
       │
┌──────▼──────┐
│     Go      │
│  Runtime    │
│ (Goroutines)│
└─────────────┘
```

## Prerequisites

Before building this extension, ensure you have:

- **Go** 1.18 or higher ([Download](https://golang.org/dl/))
- **PHP** 7.4 or higher with development headers
- **PHP Development Tools**: `phpize`, `php-config`
- **C Compiler**: GCC or Clang
- **Build Tools**: `make`, `autoconf`

### Installing Prerequisites

**Ubuntu/Debian:**
```bash
sudo apt-get update
sudo apt-get install -y php-dev php-pear build-essential golang
```

**CentOS/RHEL:**
```bash
sudo yum install -y php-devel php-pear gcc make golang
```

**macOS:**
```bash
brew install php go
```

## Installation

### Quick Build

```bash
# Clone the repository
git clone https://github.com/mucan54/php_go_goroutines.git
cd php_go_goroutines

# Build and install
make build
sudo make install
```

### Manual Build

```bash
# 1. Build the Go shared library
cd go_src
go build -buildmode=c-shared -o libgoroutines.so goroutines.go
cd ..

# 2. Build the PHP extension
phpize
./configure --enable-go-goroutines
make

# 3. Install the extension
sudo make install
```

### Enable the Extension

Add the following line to your `php.ini`:

```ini
extension=go_goroutines.so
```

Or load it dynamically:

```bash
php -d extension=go_goroutines.so your_script.php
```

### Verify Installation

```bash
php -m | grep go_goroutines
```

Or check extension info:

```bash
php --ri go_goroutines
```

## API Reference

### Functions

#### `go_start_goroutine(): int`

Start a simple goroutine that completes after a short delay.

**Returns:** Goroutine ID

**Example:**
```php
$id = go_start_goroutine();
echo "Started goroutine: $id\n";
```

---

#### `go_start_goroutine_with_task(string $task): int`

Start a goroutine with a specific task description.

**Parameters:**
- `$task` - Description of the task to execute

**Returns:** Goroutine ID

**Example:**
```php
$id = go_start_goroutine_with_task("Process user data");
```

---

#### `go_check_status(int $id): int`

Check the current status of a goroutine.

**Parameters:**
- `$id` - Goroutine ID

**Returns:**
- `-1` - Goroutine not found
- `0` - Still running
- `1` - Completed

**Example:**
```php
$status = go_check_status($id);
if ($status === 1) {
    echo "Goroutine completed!\n";
}
```

---

#### `go_get_result(int $id): string`

Get the result of a completed goroutine.

**Parameters:**
- `$id` - Goroutine ID

**Returns:** Result string or error message

**Example:**
```php
if (go_check_status($id) === 1) {
    $result = go_get_result($id);
    echo "Result: $result\n";
}
```

---

#### `go_wait(int $id, int $timeout_ms = 5000): bool`

Wait for a goroutine to complete with optional timeout.

**Parameters:**
- `$id` - Goroutine ID
- `$timeout_ms` - Timeout in milliseconds (default: 5000)

**Returns:** `true` if completed, `false` on timeout or error

**Example:**
```php
if (go_wait($id, 10000)) {
    echo "Goroutine completed within timeout\n";
    $result = go_get_result($id);
} else {
    echo "Timeout or error\n";
}
```

---

#### `go_cleanup(int $id): void`

Clean up resources associated with a goroutine. Always call this after processing results.

**Parameters:**
- `$id` - Goroutine ID

**Example:**
```php
go_cleanup($id);
```

---

#### `go_get_stats(): string`

Get statistics about all managed goroutines.

**Returns:** Statistics string with counts and status

**Example:**
```php
echo go_get_stats();
// Output: Total: 10, Active: 3, Completed: 7, Failed: 0, Go Routines: 15
```

---

#### `go_start_delayed(int $delay_ms): int`

Start a goroutine that completes after a specified delay.

**Parameters:**
- `$delay_ms` - Delay in milliseconds

**Returns:** Goroutine ID

**Example:**
```php
$id = go_start_delayed(1000); // Complete after 1 second
```

## Usage Examples

### Basic Usage

```php
<?php

// Start a goroutine
$id = go_start_goroutine();

// Wait for it to complete
if (go_wait($id, 5000)) {
    $result = go_get_result($id);
    echo "Result: $result\n";
    go_cleanup($id);
}
```

### Concurrent API Calls Simulation

```php
<?php

// Start multiple goroutines concurrently
$apis = [
    'users' => go_start_goroutine_with_task("Fetch users"),
    'products' => go_start_goroutine_with_task("Fetch products"),
    'orders' => go_start_goroutine_with_task("Fetch orders"),
];

// Wait for all to complete
foreach ($apis as $name => $id) {
    if (go_wait($id, 10000)) {
        $result = go_get_result($id);
        echo "$name: $result\n";
        go_cleanup($id);
    }
}
```

### Background Task Processing

```php
<?php

// Start a long-running task
$taskId = go_start_delayed(5000);

echo "Task started, continuing with other work...\n";

// Do other work here
sleep(1);

// Check if task is done
if (go_check_status($taskId) === 1) {
    echo "Task completed early!\n";
} else {
    echo "Task still running, waiting...\n";
    go_wait($taskId);
}

$result = go_get_result($taskId);
echo "Result: $result\n";
go_cleanup($taskId);
```

### Monitoring Statistics

```php
<?php

// Start several tasks
$tasks = [];
for ($i = 0; $i < 10; $i++) {
    $tasks[] = go_start_delayed(rand(100, 1000));
}

// Monitor progress
while (true) {
    echo go_get_stats() . "\n";

    // Check if all are done
    $allDone = true;
    foreach ($tasks as $id) {
        if (go_check_status($id) !== 1) {
            $allDone = false;
            break;
        }
    }

    if ($allDone) break;
    usleep(100000); // Wait 100ms
}

// Cleanup
foreach ($tasks as $id) {
    go_cleanup($id);
}
```

## Running Tests

```bash
# Build the extension first
make build

# Run all tests
make test

# Or run individual tests
php -d extension=modules/go_goroutines.so tests/basic_test.php
php -d extension=modules/go_goroutines.so tests/concurrent_test.php
php -d extension=modules/go_goroutines.so examples/demo.php
```

## Performance Considerations

### When to Use Go Goroutines

✅ **Good Use Cases:**
- I/O-bound operations (API calls, file operations)
- Parallel data processing
- Background task execution
- Multiple independent operations
- High-concurrency scenarios

❌ **Not Recommended For:**
- CPU-intensive PHP code (PHP's GIL limits CPU parallelism)
- Very short tasks (overhead may exceed benefits)
- Simple sequential operations

### Best Practices

1. **Always Cleanup**: Call `go_cleanup()` after processing results to free resources
2. **Set Reasonable Timeouts**: Use appropriate timeout values in `go_wait()`
3. **Monitor Statistics**: Use `go_get_stats()` to track goroutine usage
4. **Handle Errors**: Check return values and status codes
5. **Limit Concurrency**: Don't spawn unlimited goroutines; use a pool pattern

### Memory Management

- Go strings returned to PHP are copied into PHP's memory management
- Original Go-allocated strings are automatically freed
- Always call `go_cleanup()` to release goroutine tracking resources

## Troubleshooting

### Extension Not Loading

```bash
# Check if extension file exists
ls -l $(php-config --extension-dir)/go_goroutines.so

# Check PHP error log
php -d extension=go_goroutines.so -r "phpinfo();" | grep go_goroutines
```

### Build Errors

**Error: "Go compiler not found"**
```bash
# Install Go
wget https://golang.org/dl/go1.21.0.linux-amd64.tar.gz
sudo tar -C /usr/local -xzf go1.21.0.linux-amd64.tar.gz
export PATH=$PATH:/usr/local/go/bin
```

**Error: "phpize not found"**
```bash
# Install PHP development headers
sudo apt-get install php-dev  # Debian/Ubuntu
sudo yum install php-devel    # CentOS/RHEL
```

### Runtime Issues

**Goroutines timing out:**
- Increase timeout values
- Check system resources
- Monitor with `go_get_stats()`

**Memory leaks:**
- Ensure all goroutines are cleaned up with `go_cleanup()`
- Check for goroutines that never complete

## Architecture Details

### Cgo Bridge

The extension uses Cgo to export Go functions to C, which are then called from PHP:

```go
//export StartGoroutine
func StartGoroutine() C.int {
    // Go code here
}
```

### Thread Safety

- All goroutine results are protected by `sync.RWMutex`
- Unique IDs are generated with `sync.Mutex`
- Safe for concurrent access from multiple PHP requests

### Memory Model

1. **PHP → Go**: Strings are copied using `C.GoString()`
2. **Go → PHP**: Results are allocated with `C.CString()` and copied to PHP memory
3. **Cleanup**: Go strings are freed explicitly, Go manages goroutine memory

## Limitations

- Cannot directly pass PHP callbacks to goroutines (would require complex serialization)
- CPU-bound PHP code won't benefit due to PHP's Global Interpreter Lock
- Goroutines execute Go code, not PHP code directly
- Results must be serializable to strings

## Future Enhancements

Potential features for future versions:

- [ ] Channel-based communication for streaming results
- [ ] Goroutine cancellation support
- [ ] Custom timeout per goroutine
- [ ] Error handling with stack traces
- [ ] Integration with PHP's async frameworks
- [ ] Support for binary data transfer
- [ ] Goroutine pools and worker management

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Add tests for new functionality
4. Ensure all tests pass
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Credits

Developed as a bridge between PHP's ecosystem and Go's concurrency model, demonstrating the power of cross-language interoperability.

## Support

- **Issues**: [GitHub Issues](https://github.com/mucan54/php_go_goroutines/issues)
- **Documentation**: [GitHub Wiki](https://github.com/mucan54/php_go_goroutines/wiki)
- **Discussion**: [GitHub Discussions](https://github.com/mucan54/php_go_goroutines/discussions)

## Related Projects

- [PHP-CPP](https://www.php-cpp.com/) - C++ wrapper for PHP extensions
- [php-go](https://github.com/deuill/go-php) - Alternative Go-PHP binding
- [RoadRunner](https://roadrunner.dev/) - Go application server for PHP

---

**Note**: This is an experimental extension demonstrating Go-PHP interoperability. Test thoroughly before using in production environments.
