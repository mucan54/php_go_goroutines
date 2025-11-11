# Executing PHP Code in Goroutines

The Go Goroutines PECL extension now supports **actual PHP code execution** within goroutines! This allows you to run PHP code concurrently using Go's powerful concurrency model.

## How It Works

The extension executes PHP code in goroutines by:
1. Accepting PHP code as a string
2. Writing it to a temporary file
3. Executing it using PHP CLI in a goroutine
4. Capturing and returning the output

This approach provides true concurrent PHP execution while maintaining stability.

## New Functions

### `go_execute_php_code(string $php_code): int`

Execute inline PHP code in a goroutine.

```php
$code = '<?php
$sum = 0;
for ($i = 1; $i <= 100; $i++) {
    $sum += $i;
}
echo "Sum: " . $sum;
?>';

$id = go_execute_php_code($code);
go_wait($id, 5000);
echo go_get_result($id); // Output: Sum: 5050
go_cleanup($id);
```

**Parameters:**
- `$php_code` - PHP code to execute (must include `<?php` tags)

**Returns:** Goroutine ID

---

### `go_execute_php_file(string $file_path): int`

Execute a PHP file in a goroutine.

```php
// Create a PHP file
file_put_contents('/tmp/task.php', '<?php echo "Hello from file!"; ?>');

$id = go_execute_php_file('/tmp/task.php');
go_wait($id, 5000);
echo go_get_result($id); // Output: Hello from file!
go_cleanup($id);
```

**Parameters:**
- `$file_path` - Path to PHP file

**Returns:** Goroutine ID

---

### `go_execute_php_function(string $function_call): int`

Execute a PHP expression or function call in a goroutine.

```php
$id = go_execute_php_function('echo json_encode(["status" => "ok"]);');
go_wait($id, 5000);
echo go_get_result($id); // Output: {"status":"ok"}
go_cleanup($id);
```

**Parameters:**
- `$function_call` - PHP expression/function call (without `<?php` tags)

**Returns:** Goroutine ID

---

### `go_cleanup_temp_files(): void`

Clean up all temporary files created during PHP execution.

```php
go_cleanup_temp_files();
```

## Usage Examples

### Example 1: Parallel Calculations

```php
// Run multiple calculations concurrently
$tasks = [
    '<?php echo "Factorial 10: " . array_product(range(1, 10)); ?>',
    '<?php echo "Sum 1-1000: " . array_sum(range(1, 1000)); ?>',
    '<?php echo "Average: " . (array_sum(range(1, 100)) / 100); ?>',
];

$ids = [];
foreach ($tasks as $code) {
    $ids[] = go_execute_php_code($code);
}

// Wait and collect results
foreach ($ids as $id) {
    go_wait($id, 5000);
    echo go_get_result($id) . "\n";
    go_cleanup($id);
}
```

### Example 2: Concurrent API Processing

```php
// Simulate multiple API calls
$apis = ['users', 'products', 'orders'];
$tasks = [];

foreach ($apis as $api) {
    $code = "<?php
        usleep(200000); // Simulate API delay
        echo json_encode(['api' => '$api', 'status' => 'success']);
    ?>";

    $tasks[$api] = go_execute_php_code($code);
}

// Process results as they complete
$results = [];
foreach ($tasks as $api => $id) {
    go_wait($id, 10000);
    $results[$api] = json_decode(go_get_result($id), true);
    go_cleanup($id);
}

print_r($results);
```

### Example 3: Background Data Processing

```php
// Start background tasks
$taskId = go_execute_php_code('<?php
    // Heavy processing
    $data = range(1, 10000);
    $result = array_sum(array_map(function($x) {
        return $x * $x;
    }, $data));
    echo "Sum of squares: " . $result;
?>');

// Do other work while task runs
echo "Main application continues...\n";
sleep(1);

// Check if complete
if (go_check_status($taskId) === 1) {
    echo go_get_result($taskId) . "\n";
    go_cleanup($taskId);
}
```

### Example 4: File Processing

```php
// Process multiple files concurrently
$files = ['file1.txt', 'file2.txt', 'file3.txt'];
$tasks = [];

foreach ($files as $file) {
    $code = "<?php
        if (file_exists('$file')) {
            \$lines = count(file('$file'));
            \$size = filesize('$file');
            echo '$file: \$lines lines, \$size bytes';
        } else {
            echo '$file: not found';
        }
    ?>";

    $tasks[] = go_execute_php_code($code);
}

foreach ($tasks as $id) {
    go_wait($id, 5000);
    echo go_get_result($id) . "\n";
    go_cleanup($id);
}
```

## Performance Considerations

### Advantages
- ✅ **True Concurrency**: Multiple PHP processes run in parallel
- ✅ **Isolated Execution**: Each goroutine has its own PHP process
- ✅ **No Thread Safety Issues**: Separate processes avoid PHP threading problems
- ✅ **Crash Isolation**: One goroutine's failure doesn't affect others

### Overhead
- ⚠️ **Process Spawning**: Each execution spawns a new PHP process (~10-50ms overhead)
- ⚠️ **Temp Files**: Creates temporary files (cleaned up automatically)
- ⚠️ **Memory**: Each PHP process uses memory (~10-30MB)

### When to Use
✅ **Good for:**
- I/O-bound operations (file processing, API calls)
- Long-running calculations
- Independent tasks that can run in parallel
- Batch processing

❌ **Not ideal for:**
- Very short tasks (<50ms) where overhead exceeds benefits
- Tasks requiring shared state between executions
- Extremely high-frequency operations

## Best Practices

1. **Batch Similar Tasks**
   ```php
   // Good: Batch multiple operations
   $code = '<?php
       $result1 = process_data_1();
       $result2 = process_data_2();
       echo json_encode([$result1, $result2]);
   ?>';
   ```

2. **Clean Up Regularly**
   ```php
   // Clean up after batch operations
   go_cleanup_temp_files();
   ```

3. **Set Appropriate Timeouts**
   ```php
   // Long-running task? Increase timeout
   go_wait($id, 30000); // 30 seconds
   ```

4. **Handle Errors Gracefully**
   ```php
   if (go_wait($id, 5000)) {
       $result = go_get_result($id);
       if (strpos($result, 'Error:') === 0) {
           // Handle error
       }
   }
   ```

5. **Limit Concurrent Executions**
   ```php
   // Use a pool pattern to limit concurrent tasks
   $maxConcurrent = 10;
   $active = [];

   foreach ($tasks as $code) {
       if (count($active) >= $maxConcurrent) {
           // Wait for one to complete
           $id = array_shift($active);
           go_wait($id);
           go_cleanup($id);
       }
       $active[] = go_execute_php_code($code);
   }
   ```

## Error Handling

PHP errors and exceptions are captured in the output:

```php
$code = '<?php
throw new Exception("Test error");
?>';

$id = go_execute_php_code($code);
go_wait($id, 5000);
$result = go_get_result($id);

// Result will contain error output
if (strpos($result, 'Fatal error') !== false) {
    echo "PHP error occurred: $result\n";
}

go_cleanup($id);
```

## Limitations

1. **No Shared State**: Each execution is isolated
   - Cannot share variables between goroutines
   - Use database, cache, or files for shared data

2. **Autoloading**: Each execution needs complete code
   ```php
   // Include necessary files in your code
   $code = '<?php
       require_once "vendor/autoload.php";
       // Your code here
   ?>';
   ```

3. **Output Only**: Can only return what's echoed/printed
   - Use `echo` or `print` to return data
   - Consider JSON for complex data

4. **Current Directory**: May differ from calling script
   ```php
   // Use absolute paths
   $code = '<?php
       chdir("/path/to/working/dir");
       require "file.php";
   ?>';
   ```

## Comparison with Other Methods

| Method | Concurrency | Overhead | Shared State | Complexity |
|--------|-------------|----------|--------------|------------|
| Go Goroutines | ✅ True | Medium | ❌ No | Low |
| Laravel Queues | ⚠️ Workers | High | ✅ DB | Medium |
| ReactPHP | ⚠️ Async | Low | ✅ Yes | High |
| Swoole | ✅ True | Low | ✅ Yes | High |
| `exec()` | ✅ True | High | ❌ No | Low |

## Testing

Run the comprehensive tests:

```bash
php -d extension=go_goroutines.so tests/php_execution_test.php
php -d extension=go_goroutines.so examples/php_execution_demo.php
```

## Troubleshooting

**"PHP execution failed"**
- Ensure `php` is in your PATH
- Check PHP syntax in your code
- Verify file permissions for temp directory

**Slow performance**
- Reduce process spawning overhead by batching operations
- Increase concurrent task limit
- Check if tasks are truly parallelizable

**Memory issues**
- Limit number of concurrent executions
- Clean up completed goroutines promptly
- Use `go_cleanup_temp_files()` periodically

## Summary

The PHP code execution feature enables true concurrent PHP processing using Go's goroutine model. While there's overhead from process spawning, the benefits of parallel execution make it ideal for I/O-bound and long-running tasks.

For questions or issues, see [GitHub Issues](https://github.com/mucan54/php_go_goroutines/issues).
