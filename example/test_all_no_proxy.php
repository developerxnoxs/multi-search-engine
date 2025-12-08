<?php

require __DIR__ . '/../vendor/autoload.php';

use SearchEngine\GoogleSearch;
use SearchEngine\BingSearch;
use SearchEngine\DuckDuckGoSearch;
use SearchEngine\YahooSearch;
use SearchEngine\MojeekSearch;
use SearchEngine\BraveSearch;

echo "=== Test All Search Engines WITHOUT Proxy ===\n\n";

$engines = [
    'Google' => new GoogleSearch(),
    'Bing' => new BingSearch(),
    'DuckDuckGo' => new DuckDuckGoSearch(),
    'Yahoo' => new YahooSearch(),
    'Mojeek' => new MojeekSearch(),
    'Brave' => new BraveSearch(),
];

$query = 'PHP tutorial';
$numResults = 3;

$results = [];

foreach ($engines as $name => $engine) {
    echo "Testing $name...\n";
    
    $engine->setAutoFallback(false);
    
    $startTime = microtime(true);
    $engine->search($query, $numResults);
    $duration = round((microtime(true) - $startTime) * 1000);
    
    $results[$name] = [
        'count' => $engine->count(),
        'http_code' => $engine->getHttpCode(),
        'error' => $engine->getError(),
        'duration_ms' => $duration,
        'data' => $engine->data()
    ];
    
    usleep(500000);
}

echo "\n=== RESULTS ===\n\n";

foreach ($results as $name => $result) {
    $status = $result['count'] > 0 ? "OK" : "FAILED";
    echo sprintf("%-12s | HTTP: %3d | Results: %d | Time: %4dms | Status: %s\n",
        $name,
        $result['http_code'],
        $result['count'],
        $result['duration_ms'],
        $status
    );
    
    if ($result['error']) {
        echo "             | Error: " . $result['error'] . "\n";
    }
    
    if ($result['count'] > 0 && isset($result['data'][0])) {
        echo "             | First: " . substr($result['data'][0]['title'], 0, 50) . "...\n";
    }
    echo "\n";
}

$successCount = count(array_filter($results, fn($r) => $r['count'] > 0));
echo "=== Summary: $successCount/" . count($results) . " engines returned results ===\n";
