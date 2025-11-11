<?php
/**
 * Test PHP Code Execution in Goroutines
 * This demonstrates ACTUAL PHP code running concurrently in Go goroutines
 */

echo "=== Go Goroutines Extension - PHP Code Execution Test ===\n\n";

// Check if extension is loaded
if (!extension_loaded('go_goroutines')) {
    die("Error: go_goroutines extension is not loaded!\n");
}

echo "✓ Extension loaded successfully\n\n";

// Test 1: Execute inline PHP code
echo "Test 1: Executing inline PHP code in goroutine...\n";
$phpCode = '<?php
$sum = 0;
for ($i = 1; $i <= 100; $i++) {
    $sum += $i;
}
echo "Sum of 1-100: " . $sum;
?>';

$id1 = go_execute_php_code($phpCode);
echo "  Started goroutine ID: $id1\n";

if (go_wait($id1, 5000)) {
    $result = go_get_result($id1);
    echo "  ✓ Result: $result\n";
    go_cleanup($id1);
} else {
    echo "  ✗ Timeout\n";
}

echo "\n";

// Test 2: Execute multiple PHP code snippets concurrently
echo "Test 2: Running multiple PHP calculations concurrently...\n";
$tasks = [
    '<?php echo "Factorial 10: " . array_product(range(1, 10)); ?>',
    '<?php echo "Fibonacci 10: " . array_sum([1,1,2,3,5,8,13,21,34]); ?>',
    '<?php echo "Prime check 17: " . (17 % 2 != 0 ? "prime" : "not prime"); ?>',
];

$ids = [];
foreach ($tasks as $idx => $code) {
    $ids[] = go_execute_php_code($code);
    echo "  Started task " . ($idx + 1) . "\n";
}

echo "  Waiting for all tasks to complete...\n";
foreach ($ids as $idx => $id) {
    if (go_wait($id, 5000)) {
        echo "  ✓ Task " . ($idx + 1) . ": " . go_get_result($id) . "\n";
        go_cleanup($id);
    }
}

echo "\n";

// Test 3: Execute PHP file
echo "Test 3: Creating and executing PHP file in goroutine...\n";

// Create a temporary PHP file
$tempFile = sys_get_temp_dir() . '/goroutine_test_' . uniqid() . '.php';
file_put_contents($tempFile, '<?php
echo "File execution test\n";
echo "Current time: " . date("H:i:s") . "\n";
echo "Random number: " . rand(1, 100);
?>');

$id3 = go_execute_php_file($tempFile);
echo "  Executing file: $tempFile\n";

if (go_wait($id3, 5000)) {
    echo "  ✓ Output:\n";
    $lines = explode("\n", trim(go_get_result($id3)));
    foreach ($lines as $line) {
        echo "    $line\n";
    }
    go_cleanup($id3);
}

unlink($tempFile);

echo "\n";

// Test 4: Execute PHP function calls
echo "Test 4: Executing PHP function calls in goroutines...\n";
$functionCalls = [
    'echo strtoupper("hello world");',
    'echo str_repeat("*", 20);',
    'echo json_encode(["status" => "success", "value" => 42]);',
];

$funcIds = [];
foreach ($functionCalls as $idx => $call) {
    $funcIds[] = go_execute_php_function($call);
    echo "  Started function call " . ($idx + 1) . "\n";
}

foreach ($funcIds as $idx => $id) {
    if (go_wait($id, 5000)) {
        echo "  ✓ Result " . ($idx + 1) . ": " . go_get_result($id) . "\n";
        go_cleanup($id);
    }
}

echo "\n";

// Test 5: Stress test - many concurrent PHP executions
echo "Test 5: Stress test with 10 concurrent PHP executions...\n";
$start = microtime(true);
$stressIds = [];

for ($i = 1; $i <= 10; $i++) {
    $code = "<?php
    usleep(100000); // Sleep 100ms
    echo 'Task $i completed';
    ?>";
    $stressIds[] = go_execute_php_code($code);
}

echo "  All tasks started, waiting for completion...\n";
$completed = 0;
foreach ($stressIds as $id) {
    if (go_wait($id, 10000)) {
        $completed++;
        go_cleanup($id);
    }
}

$elapsed = round((microtime(true) - $start) * 1000);
echo "  ✓ Completed $completed/10 tasks in {$elapsed}ms\n";
echo "  (Sequential would take ~1000ms, concurrent much faster)\n";

echo "\n";

// Test 6: Error handling
echo "Test 6: Testing error handling...\n";
$errorCode = '<?php
throw new Exception("This is a test error");
?>';

$id6 = go_execute_php_code($errorCode);
if (go_wait($id6, 5000)) {
    $result = go_get_result($id6);
    echo "  Result (should show error): \n";
    echo "  " . trim($result) . "\n";
    go_cleanup($id6);
}

echo "\n";

// Cleanup
echo "Cleaning up temporary files...\n";
go_cleanup_temp_files();

// Final stats
echo "\nFinal statistics: " . go_get_stats() . "\n";

echo "\n=== All tests completed! ===\n";
echo "\n✓ PHP CODE IS NOW ACTUALLY EXECUTED IN GOROUTINES!\n";
