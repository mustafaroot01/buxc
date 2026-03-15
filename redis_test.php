<?php
// Redis Verbose Test Script
$host = '127.0.0.1';
$port = 6379;
$pass = 'Buxc2026Redis'; // الكلمة التي حاولنا استخدامها

echo "--- Redis Diagnostic ---\n";
echo "Connecting to $host:$port...\n";

try {
    $redis = new Redis();
    $connect = $redis->connect($host, $port, 2.0);
    
    if (!$connect) {
        die("❌ ERROR: Could not connect to any Redis server on $host:$port\n");
    }

    echo "✅ Connection established. Testing Authentication...\n";

    // Test with the password
    try {
        if ($redis->auth($pass)) {
            echo "✅ SUCCESS: Authenticated with password '$pass'!\n";
        } else {
            echo "❌ FAILED: Authentication rejected for password '$pass'.\n";
        }
    } catch (Exception $authEx) {
        echo "❌ AUTH ERROR: " . $authEx->getMessage() . "\n";
    }

    // Diagnostic: Try without password
    try {
        $redisNoAuth = new Redis();
        $redisNoAuth->connect($host, $port, 1.0);
        $ping = $redisNoAuth->ping();
        echo "🤔 DIAGNOSTIC: Server responded to PING without password? " . ($ping ? "YES (Security Risk!)" : "NO") . "\n";
    } catch (Exception $pingEx) {
        echo "ℹ️  DIAGNOSTIC: Ping without auth failed as expected: " . $pingEx->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
}
echo "------------------------\n";
