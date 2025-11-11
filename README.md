# Go Goroutines PECL Extension for PHP

A high-performance PHP extension that brings Go's lightweight concurrency model to PHP through goroutines. **Execute actual PHP code concurrently** using Go's powerful runtime for improved I/O-bound and parallel processing performance.

## Features

- **Execute PHP Code in Goroutines**: Run actual PHP code, files, and functions concurrently
- **True Concurrency**: Multiple PHP processes execute in parallel via Go goroutines
- **High Performance**: Leverage Go's efficient goroutine scheduler and runtime
- **Isolated Execution**: Each goroutine runs in its own PHP process (crash-safe)
- **Synchronization Primitives**: Wait for goroutines, check status, and retrieve results
- **Thread-Safe**: Proper synchronization between PHP and Go runtimes
- **Easy to Use**: Simple PHP API for complex concurrency patterns
- **Resource Management**: Built-in cleanup and statistics tracking

## Architecture

This extension uses **Cgo** to bridge PHP's Zend Engine with Go's runtime, allowing PHP to:
- **Execute PHP code concurrently** in separate processes via goroutines
- Start goroutines that execute independently
- Monitor goroutine execution status
- Retrieve results from completed goroutines
- Manage goroutine lifecycle and cleanup

```
┌─────────────────────────────────────┐
│           PHP Application           │
│         (Zend Engine)               │
└──────────────┬──────────────────────┘
               │
               │ C Bridge (Cgo)
               │
┌──────────────▼──────────────────────┐
│         Go Runtime                  │
│  ┌──────────┐  ┌──────────┐        │
│  │Goroutine │  │Goroutine │  ...   │
│  │ PHP CLI  │  │ PHP CLI  │        │
│  └──────────┘  └──────────┘        │
└─────────────────────────────────────┘
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

## Quick Start

Execute PHP code concurrently in just a few lines:

```php
<?php
// Execute multiple PHP tasks concurrently
$task1 = go_execute_php_code('<?php echo "Sum: " . array_sum(range(1, 100)); ?>');
$task2 = go_execute_php_code('<?php echo "Product: " . array_product(range(1, 5)); ?>');
$task3 = go_execute_php_code('<?php echo "JSON: " . json_encode(["status" => "ok"]); ?>');

// Wait and get results
go_wait($task1, 5000);
echo go_get_result($task1) . "\n";  // Output: Sum: 5050
go_cleanup($task1);

go_wait($task2, 5000);
echo go_get_result($task2) . "\n";  // Output: Product: 120
go_cleanup($task2);

go_wait($task3, 5000);
echo go_get_result($task3) . "\n";  // Output: JSON: {"status":"ok"}
go_cleanup($task3);
```

**That's it!** Your PHP code now runs concurrently in Go goroutines. 🚀

For more examples, see [PHP_EXECUTION.md](PHP_EXECUTION.md).

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

#### `go_execute_php_code(string $php_code): int` ⭐ NEW

**Execute actual PHP code in a goroutine.** This runs your PHP code in a separate process concurrently.

**Parameters:**
- `$php_code` - PHP code to execute (must include `<?php` tags)

**Returns:** Goroutine ID

**Example:**
```php
$code = '<?php
$sum = array_sum(range(1, 100));
echo "Sum: $sum";
?>';

$id = go_execute_php_code($code);
go_wait($id, 5000);
echo go_get_result($id); // Output: Sum: 5050
go_cleanup($id);
```

---

#### `go_execute_php_file(string $file_path): int` ⭐ NEW

**Execute a PHP file in a goroutine.**

**Parameters:**
- `$file_path` - Path to the PHP file to execute

**Returns:** Goroutine ID

**Example:**
```php
$id = go_execute_php_file('/path/to/script.php');
go_wait($id, 5000);
$result = go_get_result($id);
go_cleanup($id);
```

---

#### `go_execute_php_function(string $expression): int` ⭐ NEW

**Execute a PHP expression or function call in a goroutine.**

**Parameters:**
- `$expression` - PHP expression to execute (without `<?php` tags)

**Returns:** Goroutine ID

**Example:**
```php
$id = go_execute_php_function('echo json_encode(["status" => "ok"]);');
go_wait($id, 5000);
echo go_get_result($id); // Output: {"status":"ok"}
go_cleanup($id);
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

---

#### `go_cleanup_temp_files(): void` ⭐ NEW

Clean up temporary files created during PHP code execution.

**Example:**
```php
go_cleanup_temp_files();
```

## Usage Examples

### PHP Code Execution (Primary Feature)

```php
<?php

// Execute PHP code concurrently
$code = '<?php
$data = range(1, 1000);
$sum = array_sum($data);
echo "Sum: $sum";
?>';

$id = go_execute_php_code($code);

// Wait for it to complete
if (go_wait($id, 5000)) {
    $result = go_get_result($id);
    echo "Result: $result\n"; // Output: Sum: 500500
    go_cleanup($id);
}
```

### Concurrent PHP Execution

```php
<?php

// Run multiple PHP scripts concurrently
$tasks = [
    '<?php echo "Task 1: " . (10 * 10); ?>',
    '<?php echo "Task 2: " . strtoupper("hello"); ?>',
    '<?php echo "Task 3: " . json_encode(["status" => "ok"]); ?>',
];

$ids = [];
foreach ($tasks as $code) {
    $ids[] = go_execute_php_code($code);
}

// Collect all results
foreach ($ids as $id) {
    go_wait($id, 5000);
    echo go_get_result($id) . "\n";
    go_cleanup($id);
}
```

### Basic Usage

```php
<?php

// Start a simple goroutine
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
php -d extension=modules/go_goroutines.so tests/php_execution_test.php  # PHP code execution tests
php -d extension=modules/go_goroutines.so examples/demo.php
php -d extension=modules/go_goroutines.so examples/php_execution_demo.php  # PHP execution examples
```

## Performance Considerations

### PHP Code Execution Performance

**Overhead:**
- Process spawning: ~10-50ms per execution
- Memory per process: ~10-30MB
- Temp file I/O: minimal overhead

**Benefits:**
- ✅ True concurrency through separate PHP processes
- ✅ Isolated execution (crash-safe)
- ✅ No PHP thread-safety concerns
- ✅ Parallel I/O operations scale linearly

### When to Use Go Goroutines

✅ **Good Use Cases:**
- **PHP code execution**: Multiple PHP scripts that can run in parallel
- I/O-bound operations (API calls, file processing)
- Parallel data processing
- Background task execution
- Multiple independent operations
- Tasks taking >100ms (where process overhead is negligible)

❌ **Not Recommended For:**
- Very short tasks (<50ms) where overhead exceeds benefits
- Tasks requiring shared state between executions
- Simple sequential operations
- Extremely high-frequency operations (>1000/sec)

### Best Practices

1. **Always Cleanup**: Call `go_cleanup()` after processing results to free resources
2. **Clean Temp Files**: Call `go_cleanup_temp_files()` periodically when using PHP execution
3. **Set Reasonable Timeouts**: Use appropriate timeout values in `go_wait()`
4. **Monitor Statistics**: Use `go_get_stats()` to track goroutine usage
5. **Handle Errors**: Check return values and status codes
6. **Limit Concurrency**: Don't spawn unlimited goroutines; use a pool pattern (recommend max 10-50)
7. **Batch Operations**: Combine multiple operations in single PHP code execution to reduce overhead

### Memory Management

- Go strings returned to PHP are copied into PHP's memory management
- Original Go-allocated strings are automatically freed
- Always call `go_cleanup()` to release goroutine tracking resources
- Each PHP execution spawns a separate process with its own memory space

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
4. **PHP Execution**: Each goroutine spawns an independent PHP CLI process

## Limitations

- **No Shared State**: Each PHP execution is isolated (use database/cache/files for shared data)
- **Process Overhead**: ~10-50ms per PHP execution due to process spawning
- **Output Only**: Can only return what's echoed/printed from PHP code
- **Results as Strings**: All results must be serializable to strings (use JSON for complex data)
- **Current Directory**: PHP executions may have different working directories (use absolute paths)
- **No Direct Callbacks**: Cannot pass PHP closures directly (serialize code as strings instead)

## PHP Code Execution Documentation

For complete documentation on executing PHP code in goroutines, see **[PHP_EXECUTION.md](PHP_EXECUTION.md)** which includes:
- Detailed usage examples
- Performance considerations
- Error handling
- Best practices
- Troubleshooting guide

## Future Enhancements

Potential features for future versions:

- [x] ~~PHP code execution in goroutines~~ ✅ **Implemented!**
- [ ] Goroutine cancellation support
- [ ] Channel-based communication for streaming results
- [ ] Custom timeout per goroutine
- [ ] Error handling with stack traces
- [ ] Support for binary data transfer
- [ ] Goroutine pools and worker management
- [ ] Integration with Laravel/Symfony frameworks
- [ ] Persistent PHP worker processes (reduce overhead)

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
