<?php
/**
 * Practical PHP Code Execution Examples
 * Demonstrates real-world use cases for executing PHP code in goroutines
 */

echo "╔════════════════════════════════════════════════════════╗\n";
echo "║   PHP Code Execution in Go Goroutines - Demo          ║\n";
echo "╚════════════════════════════════════════════════════════╝\n\n";

if (!extension_loaded('go_goroutines')) {
    die("❌ Extension not loaded. Run: php -d extension=go_goroutines.so " . __FILE__ . "\n");
}

// Example 1: Parallel API Call Simulation
function example_parallel_api_calls() {
    echo "═══ Example 1: Parallel API Call Simulation ═══\n";
    echo "Simulating API calls that fetch and process data...\n\n";

    $apiCalls = [
        'users' => '<?php
            // Simulate API call and processing
            usleep(200000); // 200ms
            echo json_encode([
                "api" => "users",
                "count" => 150,
                "status" => "success"
            ]);
        ?>',

        'products' => '<?php
            // Simulate API call and processing
            usleep(150000); // 150ms
            echo json_encode([
                "api" => "products",
                "count" => 523,
                "status" => "success"
            ]);
        ?>',

        'orders' => '<?php
            // Simulate API call and processing
            usleep(250000); // 250ms
            echo json_encode([
                "api" => "orders",
                "count" => 89,
                "status" => "success"
            ]);
        ?>',
    ];

    $start = microtime(true);
    $tasks = [];

    // Start all API calls concurrently
    foreach ($apiCalls as $name => $code) {
        $tasks[$name] = go_execute_php_code($code);
        echo "  → Started $name API call\n";
    }

    echo "\n  Processing results...\n\n";

    // Collect results
    $results = [];
    foreach ($tasks as $name => $id) {
        if (go_wait($id, 10000)) {
            $result = json_decode(go_get_result($id), true);
            $results[$name] = $result;
            echo "  ✓ {$name}: {$result['count']} records fetched\n";
            go_cleanup($id);
        }
    }

    $elapsed = round((microtime(true) - $start) * 1000);
    $sequential = 200 + 150 + 250;
    echo "\n  Total time: {$elapsed}ms (vs {$sequential}ms sequential)\n";
    echo "  Speedup: " . round($sequential / $elapsed, 2) . "x faster!\n\n";
}

// Example 2: Data Processing Pipeline
function example_data_processing() {
    echo "═══ Example 2: Parallel Data Processing ═══\n";
    echo "Processing different datasets concurrently...\n\n";

    $dataSets = [
        'CSV Processing' => '<?php
            // Simulate CSV processing
            $data = range(1, 1000);
            $sum = array_sum($data);
            echo "Processed 1000 CSV rows, sum: $sum";
        ?>',

        'JSON Parsing' => '<?php
            // Simulate JSON parsing
            $json = json_encode(range(1, 500));
            $decoded = json_decode($json);
            echo "Parsed JSON with " . count($decoded) . " elements";
        ?>',

        'XML Validation' => '<?php
            // Simulate XML validation
            usleep(100000);
            echo "Validated XML document: 250 nodes processed";
        ?>',
    ];

    $tasks = [];
    foreach ($dataSets as $name => $code) {
        $tasks[$name] = go_execute_php_code($code);
        echo "  → Started: $name\n";
    }

    echo "\n  Results:\n";
    foreach ($tasks as $name => $id) {
        if (go_wait($id, 10000)) {
            echo "  ✓ $name: " . go_get_result($id) . "\n";
            go_cleanup($id);
        }
    }
    echo "\n";
}

// Example 3: Background Task Execution
function example_background_tasks() {
    echo "═══ Example 3: Background Task Execution ═══\n";
    echo "Running maintenance tasks in background...\n\n";

    $tasks = [
        'Cache Cleanup' => '<?php
            // Simulate cache cleanup
            usleep(150000);
            echo "Cleaned 347 expired cache entries";
        ?>',

        'Log Rotation' => '<?php
            // Simulate log rotation
            usleep(100000);
            echo "Rotated 5 log files (125MB freed)";
        ?>',

        'Session Garbage Collection' => '<?php
            // Simulate session cleanup
            usleep(200000);
            echo "Removed 89 expired sessions";
        ?>',
    ];

    $taskIds = [];
    foreach ($tasks as $name => $code) {
        $taskIds[$name] = go_execute_php_code($code);
        echo "  → Queued: $name\n";
    }

    echo "\n  Main application continues working...\n";
    echo "  (Simulating other work)\n";
    usleep(100000);

    echo "\n  Checking background tasks:\n";
    foreach ($taskIds as $name => $id) {
        $status = go_check_status($id);
        if ($status === 1) {
            echo "  ✓ $name: " . go_get_result($id) . "\n";
            go_cleanup($id);
        } else if ($status === 0) {
            echo "  ⏳ $name: Still running...\n";
            go_wait($id, 5000);
            echo "  ✓ $name: " . go_get_result($id) . "\n";
            go_cleanup($id);
        }
    }
    echo "\n";
}

// Example 4: File Operations
function example_file_operations() {
    echo "═══ Example 4: Concurrent File Operations ═══\n";
    echo "Processing multiple files in parallel...\n\n";

    // Create temp files
    $tempFiles = [];
    for ($i = 1; $i <= 3; $i++) {
        $file = sys_get_temp_dir() . "/test_file_{$i}.txt";
        file_put_contents($file, str_repeat("Line $i\n", 100));
        $tempFiles[] = $file;
    }

    $tasks = [];
    foreach ($tempFiles as $idx => $file) {
        $code = "<?php
        \$file = '$file';
        \$lines = file(\$file);
        \$count = count(\$lines);
        \$size = filesize(\$file);
        echo \"File $idx: \$count lines, \$size bytes\";
        ?>";

        $tasks[] = go_execute_php_code($code);
        echo "  → Processing file " . ($idx + 1) . "\n";
    }

    echo "\n  Results:\n";
    foreach ($tasks as $idx => $id) {
        if (go_wait($id, 5000)) {
            echo "  ✓ " . go_get_result($id) . "\n";
            go_cleanup($id);
        }
    }

    // Cleanup
    foreach ($tempFiles as $file) {
        unlink($file);
    }
    echo "\n";
}

// Example 5: Mathematical Computations
function example_computations() {
    echo "═══ Example 5: Parallel Mathematical Computations ═══\n";
    echo "Running expensive calculations concurrently...\n\n";

    $computations = [
        'Fibonacci(30)' => '<?php
            function fib($n) {
                if ($n <= 1) return $n;
                return fib($n-1) + fib($n-2);
            }
            echo "Result: " . fib(20);
        ?>',

        'Prime Numbers up to 1000' => '<?php
            $primes = [];
            for ($i = 2; $i <= 1000; $i++) {
                $isPrime = true;
                for ($j = 2; $j <= sqrt($i); $j++) {
                    if ($i % $j == 0) {
                        $isPrime = false;
                        break;
                    }
                }
                if ($isPrime) $primes[] = $i;
            }
            echo "Found " . count($primes) . " primes";
        ?>',

        'Array Operations' => '<?php
            $data = range(1, 10000);
            $sum = array_sum($data);
            $avg = $sum / count($data);
            echo "Sum: $sum, Average: $avg";
        ?>',
    ];

    $start = microtime(true);
    $tasks = [];

    foreach ($computations as $name => $code) {
        $tasks[$name] = go_execute_php_code($code);
        echo "  → Started: $name\n";
    }

    echo "\n  Computing...\n\n";
    foreach ($tasks as $name => $id) {
        if (go_wait($id, 10000)) {
            echo "  ✓ $name: " . go_get_result($id) . "\n";
            go_cleanup($id);
        }
    }

    $elapsed = round((microtime(true) - $start) * 1000);
    echo "\n  Total computation time: {$elapsed}ms\n\n";
}

// Run all examples
try {
    example_parallel_api_calls();
    echo str_repeat("─", 60) . "\n\n";

    example_data_processing();
    echo str_repeat("─", 60) . "\n\n";

    example_background_tasks();
    echo str_repeat("─", 60) . "\n\n";

    example_file_operations();
    echo str_repeat("─", 60) . "\n\n";

    example_computations();

    // Cleanup
    go_cleanup_temp_files();

    // Final stats
    echo "╔════════════════════════════════════════════════════════╗\n";
    echo "║              All examples completed!                   ║\n";
    echo "╚════════════════════════════════════════════════════════╝\n";
    echo "\nStats: " . go_get_stats() . "\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
