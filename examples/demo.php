<?php
/**
 * Demo script for Go Goroutines PHP Extension
 * This demonstrates practical use cases for the extension
 */

echo "╔════════════════════════════════════════════════════════╗\n";
echo "║   Go Goroutines PHP Extension - Demo Application      ║\n";
echo "╚════════════════════════════════════════════════════════╝\n\n";

// Check if extension is loaded
if (!extension_loaded('go_goroutines')) {
    die("❌ Error: go_goroutines extension is not loaded!\n" .
        "   Load it with: php -d extension=go_goroutines.so " . __FILE__ . "\n");
}

echo "✓ Extension loaded and ready!\n\n";

// Example 1: Simulating parallel API calls
function simulateParallelAPICalls() {
    echo "═══ Example 1: Parallel API Calls Simulation ═══\n";
    echo "Simulating 5 concurrent API requests...\n\n";

    $apis = [
        ['name' => 'User Service', 'delay' => 150],
        ['name' => 'Product Service', 'delay' => 200],
        ['name' => 'Order Service', 'delay' => 100],
        ['name' => 'Payment Service', 'delay' => 250],
        ['name' => 'Analytics Service', 'delay' => 180],
    ];

    $jobs = [];
    $start = microtime(true);

    // Start all API calls concurrently
    foreach ($apis as $api) {
        $id = go_start_goroutine_with_task("Call {$api['name']}");
        $jobs[] = ['id' => $id, 'name' => $api['name']];
        echo "  → Started: {$api['name']}\n";
    }

    echo "\n  Waiting for all APIs to respond...\n\n";

    // Wait for all to complete
    foreach ($jobs as $job) {
        if (go_wait($job['id'], 5000)) {
            $result = go_get_result($job['id']);
            echo "  ✓ {$job['name']}: $result\n";
            go_cleanup($job['id']);
        }
    }

    $elapsed = round((microtime(true) - $start) * 1000);
    echo "\n  Total time: {$elapsed}ms (concurrent execution)\n";
    echo "  Time saved vs sequential: ~" . round(750 - $elapsed) . "ms\n\n";
}

// Example 2: Background task processing
function backgroundTaskProcessing() {
    echo "═══ Example 2: Background Task Processing ═══\n";
    echo "Starting background tasks that complete at different times...\n\n";

    $tasks = [
        ['name' => 'Image Processing', 'delay' => 300],
        ['name' => 'Email Sending', 'delay' => 150],
        ['name' => 'Cache Warming', 'delay' => 400],
    ];

    $taskIds = [];

    foreach ($tasks as $task) {
        $id = go_start_delayed($task['delay']);
        $taskIds[$id] = $task;
        echo "  → Queued: {$task['name']} (est. {$task['delay']}ms)\n";
    }

    echo "\n  Main application continues...\n";
    echo "  (Checking task status periodically)\n\n";

    // Check status periodically
    $completed = 0;
    $total = count($taskIds);

    while ($completed < $total) {
        foreach ($taskIds as $id => $task) {
            if (!isset($task['completed'])) {
                $status = go_check_status($id);
                if ($status === 1) {
                    $result = go_get_result($id);
                    echo "  ✓ Completed: {$task['name']}\n";
                    $taskIds[$id]['completed'] = true;
                    $completed++;
                    go_cleanup($id);
                }
            }
        }
        usleep(50000); // Check every 50ms
    }

    echo "\n  All background tasks completed!\n\n";
}

// Example 3: Real-time statistics
function realtimeStatistics() {
    echo "═══ Example 3: Real-time Statistics Monitoring ═══\n";

    // Start several tasks
    $ids = [];
    for ($i = 1; $i <= 10; $i++) {
        $delay = rand(100, 500);
        $ids[] = go_start_delayed($delay);
    }

    echo "Started 10 goroutines with random delays (100-500ms)\n";
    echo "Monitoring statistics in real-time...\n\n";

    // Monitor for 2 seconds
    $start = microtime(true);
    $iterations = 0;

    while ((microtime(true) - $start) < 2) {
        $stats = go_get_stats();
        echo "\r  [" . round((microtime(true) - $start), 1) . "s] " . $stats;
        usleep(100000); // Update every 100ms
        $iterations++;
    }

    echo "\n\n  Cleaning up...\n";
    foreach ($ids as $id) {
        go_cleanup($id);
    }

    echo "  Final: " . go_get_stats() . "\n\n";
}

// Run all examples
try {
    simulateParallelAPICalls();
    echo str_repeat("─", 60) . "\n\n";

    backgroundTaskProcessing();
    echo str_repeat("─", 60) . "\n\n";

    realtimeStatistics();

    echo "╔════════════════════════════════════════════════════════╗\n";
    echo "║              Demo completed successfully!              ║\n";
    echo "╚════════════════════════════════════════════════════════╝\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
