<?php

// Simple API test script
// Run this with: php test-api.php

$baseUrl = 'http://localhost:8000/api';

function testEndpoint($url, $description) {
    echo "Testing: $description\n";
    echo "URL: $url\n";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            'timeout' => 10
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        echo "❌ Failed to connect\n";
    } else {
        echo "✅ Success: " . substr($response, 0, 100) . "...\n";
    }
    echo "---\n";
}

// Test health endpoint
testEndpoint("$baseUrl/health", "Health Check");

// Test leaderboard endpoints
testEndpoint("$baseUrl/leaderboard/global", "Global Leaderboard");
testEndpoint("$baseUrl/leaderboard/daily", "Daily Leaderboard");

echo "API testing complete!\n";
