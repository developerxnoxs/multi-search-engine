<?php
require __DIR__ . '/../vendor/autoload.php';

use SearchEngine\BraveSearch;

$scraperApiKey = '1820c54a47ebf6d3557d9be57aa70c81';
$query = 'PHP tutorial';

echo "=== TEST BRAVE SEARCH ===\n\n";

echo "--- Test 1: Tanpa Proxy ---\n";
$brave1 = new BraveSearch();
$brave1->setAutoFallback(false);
$brave1->setRetry(2, 1000);

$brave1->search($query, numResults: 5, timeout: 15);

if ($brave1->hasError()) {
    echo "Error: " . $brave1->getError() . "\n";
    echo "HTTP Code: " . $brave1->getHttpCode() . "\n";
} elseif ($brave1->isEmpty()) {
    echo "Tidak ada hasil ditemukan\n";
} else {
    echo "Berhasil! Ditemukan " . $brave1->count() . " hasil:\n";
    foreach ($brave1->data() as $i => $result) {
        echo ($i + 1) . ". " . $result['title'] . "\n";
        echo "   URL: " . $result['url'] . "\n";
    }
}

echo "\n--- Test 2: Dengan ScraperAPI Proxy ---\n";
$brave2 = new BraveSearch();
$brave2->setScraperApi($scraperApiKey, useAlways: true);
$brave2->setRetry(2, 2000);

$brave2->search($query, numResults: 5, timeout: 60);

if ($brave2->hasError()) {
    echo "Error: " . $brave2->getError() . "\n";
    echo "HTTP Code: " . $brave2->getHttpCode() . "\n";
} elseif ($brave2->isEmpty()) {
    echo "Tidak ada hasil ditemukan\n";
    echo "HTTP Code: " . $brave2->getHttpCode() . "\n";
} else {
    echo "Berhasil! Ditemukan " . $brave2->count() . " hasil:\n";
    foreach ($brave2->data() as $i => $result) {
        echo ($i + 1) . ". " . $result['title'] . "\n";
        echo "   URL: " . $result['url'] . "\n";
    }
}

echo "\n=== TEST SELESAI ===\n";
