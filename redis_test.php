<?php
// Redis Test Script
$host = '127.0.0.1';
$port = 6379;
$pass = 'Buxc2026Redis'; // تأكد أن هذه مطابقة لما وضعته في Docker

echo "Connecting to Redis at $host:$port...\n";

try {
    $redis = new Redis();
    $redis->connect($host, $port);
    
    if ($redis->auth($pass)) {
        echo "✅ SUCCESS: Authenticated successfully!\n";
    } else {
        echo "❌ FAILED: Authentication failed. Wrong password.\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
