<?php
require __DIR__ . '/vendor/autoload.php';

use SearchEngine\GoogleSearch;
use SearchEngine\BingSearch;
use SearchEngine\DuckDuckGoSearch;
use SearchEngine\YahooSearch;
use SearchEngine\MojeekSearch;
use SearchEngine\BraveSearch;

echo "=== Test Library Multi Search Engine ===\n\n";

$query = "PHP programming";

$engines = [
    'DuckDuckGo' => new DuckDuckGoSearch(),
    'Yahoo' => new YahooSearch(),
    'Mojeek' => new MojeekSearch(),
    'Bing' => new BingSearch(),
    'Google' => new GoogleSearch(),
    'Brave' => new BraveSearch(),
];

foreach ($engines as $name => $engine) {
    echo "--- Testing $name ---\n";
    
    $engine->setRetry(2, 500);
    $engine->search($query, numResults: 3);
    
    echo "HTTP Code: " . $engine->getHttpCode() . "\n";
    
    if ($engine->hasError()) {
        echo "Error: " . $engine->getError() . "\n";
    } elseif ($engine->isEmpty()) {
        echo "Status: No results found\n";
    } else {
        echo "Status: OK\n";
        echo "Count: " . $engine->count() . "\n";
        
        $first = $engine->first();
        if ($first) {
            echo "First result: " . substr($first['title'], 0, 50) . "...\n";
        }
    }
    
    echo "\n";
}

echo "=== Testing New Methods ===\n\n";

$ddg = new DuckDuckGoSearch();
$ddg->search('PHP framework', numResults: 5);

echo "count(): " . $ddg->count() . "\n";
echo "isEmpty(): " . ($ddg->isEmpty() ? 'true' : 'false') . "\n";
echo "hasError(): " . ($ddg->hasError() ? 'true' : 'false') . "\n";
echo "getHttpCode(): " . $ddg->getHttpCode() . "\n";

$first = $ddg->first();
echo "first() title: " . ($first ? $first['title'] : 'null') . "\n";

$last = $ddg->last();
echo "last() title: " . ($last ? $last['title'] : 'null') . "\n";

echo "\nurl() output:\n";
print_r($ddg->url());

echo "\n=== Test filterByDomain ===\n";
$filtered = $ddg->filterByDomain('php.net');
echo "Results from php.net: " . $filtered->count() . "\n";

echo "\n=== All tests completed! ===\n";
