<?php
/**
 * Concurrent execution test for Go Goroutines PHP Extension
 */

echo "=== Go Goroutines Extension - Concurrent Test ===\n\n";

// Check if extension is loaded
if (!extension_loaded('go_goroutines')) {
    die("Error: go_goroutines extension is not loaded!\n");
}

echo "Test: Starting multiple goroutines concurrently...\n\n";

// Start multiple goroutines with different delays
$goroutines = [];
$delays = [100, 200, 150, 300, 50];

$start_time = microtime(true);

foreach ($delays as $index => $delay) {
    $id = go_start_delayed($delay);
    $goroutines[] = [
        'id' => $id,
        'delay' => $delay,
        'index' => $index + 1
    ];
    echo "Started Goroutine #{$id} (will complete in ~{$delay}ms)\n";
}

echo "\nWaiting for all goroutines to complete...\n";
echo "Total goroutines: " . count($goroutines) . "\n\n";

// Show initial stats
echo "Initial stats: " . go_get_stats() . "\n\n";

// Poll for completion
$completed = [];
$timeout = 5000; // 5 seconds total timeout
$poll_start = microtime(true);

while (count($completed) < count($goroutines)) {
    // Check timeout
    if ((microtime(true) - $poll_start) * 1000 > $timeout) {
        echo "ERROR: Timeout reached!\n";
        break;
    }

    foreach ($goroutines as $gr) {
        if (in_array($gr['id'], $completed)) {
            continue;
        }

        $status = go_check_status($gr['id']);
        if ($status === 1) {
            $elapsed = round((microtime(true) - $start_time) * 1000);
            $result = go_get_result($gr['id']);
            echo "✓ Goroutine #{$gr['id']} completed after ~{$elapsed}ms: $result\n";
            $completed[] = $gr['id'];
        }
    }

    usleep(10000); // Sleep 10ms between polls
}

echo "\n";

// Cleanup
foreach ($goroutines as $gr) {
    go_cleanup($gr['id']);
}

$total_elapsed = round((microtime(true) - $start_time) * 1000);
echo "All goroutines completed in ~{$total_elapsed}ms\n";
echo "Expected time if sequential: ~" . array_sum($delays) . "ms\n";
echo "Speedup: " . round(array_sum($delays) / $total_elapsed, 2) . "x\n\n";

// Final stats
echo "Final stats: " . go_get_stats() . "\n";

echo "\n=== Concurrent test completed! ===\n";
