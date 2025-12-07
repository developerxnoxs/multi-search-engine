<?php
require __DIR__ . '/vendor/autoload.php';

use SearchEngine\GoogleSearch;
use SearchEngine\BingSearch;
use SearchEngine\DuckDuckGoSearch;
use SearchEngine\YahooSearch;
use SearchEngine\MojeekSearch;
use SearchEngine\BraveSearch;

$query = "PHP programming";
$numResults = 3;

$engines = [
    'Google' => new GoogleSearch(),
    'Bing' => new BingSearch(),
    'DuckDuckGo' => new DuckDuckGoSearch(),
    'Yahoo' => new YahooSearch(),
    'Mojeek' => new MojeekSearch(),
    'Brave' => new BraveSearch(),
];

echo "Testing all search engines with query: \"$query\"\n";
echo str_repeat("=", 60) . "\n\n";

foreach ($engines as $name => $engine) {
    echo "Testing $name...\n";
    echo str_repeat("-", 40) . "\n";
    
    try {
        $startTime = microtime(true);
        $results = $engine->search($query, $numResults)->data();
        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000);
        
        if (count($results) > 0) {
            echo "Status: OK ({$duration}ms)\n";
            echo "Results found: " . count($results) . "\n";
            foreach ($results as $i => $result) {
                echo "  " . ($i + 1) . ". " . substr($result['title'], 0, 50) . "...\n";
            }
        } else {
            echo "Status: WARNING - No results returned\n";
        }
    } catch (Exception $e) {
        echo "Status: ERROR\n";
        echo "Message: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo str_repeat("=", 60) . "\n";
echo "Test completed.\n";
