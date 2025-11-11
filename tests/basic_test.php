<?php
/**
 * Basic test for Go Goroutines PHP Extension
 */

echo "=== Go Goroutines Extension - Basic Test ===\n\n";

// Check if extension is loaded
if (!extension_loaded('go_goroutines')) {
    die("Error: go_goroutines extension is not loaded!\n");
}

echo "✓ Extension loaded successfully\n\n";

// Test 1: Start a simple goroutine
echo "Test 1: Starting a simple goroutine...\n";
$id1 = go_start_goroutine();
echo "  Goroutine started with ID: $id1\n";

// Check status immediately (should be running or done quickly)
$status = go_check_status($id1);
echo "  Initial status: $status (0=running, 1=done, -1=not found)\n";

// Wait for completion
echo "  Waiting for completion...\n";
if (go_wait($id1, 2000)) {
    echo "  ✓ Goroutine completed!\n";
    $result = go_get_result($id1);
    echo "  Result: $result\n";
    go_cleanup($id1);
} else {
    echo "  ✗ Timeout or error\n";
}

echo "\n";

// Test 2: Start goroutine with task
echo "Test 2: Starting goroutine with custom task...\n";
$id2 = go_start_goroutine_with_task("Process user data");
echo "  Goroutine started with ID: $id2\n";

// Wait for completion
if (go_wait($id2, 3000)) {
    echo "  ✓ Task completed!\n";
    $result = go_get_result($id2);
    echo "  Result: $result\n";
    go_cleanup($id2);
} else {
    echo "  ✗ Timeout or error\n";
}

echo "\n";

// Test 3: Delayed execution
echo "Test 3: Testing delayed execution...\n";
$start = microtime(true);
$id3 = go_start_delayed(500); // 500ms delay
echo "  Started delayed goroutine with ID: $id3\n";

if (go_wait($id3, 2000)) {
    $elapsed = round((microtime(true) - $start) * 1000);
    echo "  ✓ Completed after ~{$elapsed}ms\n";
    $result = go_get_result($id3);
    echo "  Result: $result\n";
    go_cleanup($id3);
}

echo "\n";

// Test 4: Get statistics
echo "Test 4: Getting goroutine statistics...\n";
$stats = go_get_stats();
echo "  Stats: $stats\n";

echo "\n=== All tests completed! ===\n";
