<?php
require __DIR__ . '/../vendor/autoload.php';

use SearchEngine\BraveSearch;
use SearchEngine\FileCache;

echo "=== TEST CACHING & RATE LIMITING ===\n\n";

$cache = new FileCache('/tmp/search_cache', 3600);

echo "--- Test 1: Pencarian Pertama (tanpa cache) ---\n";
$search = new BraveSearch();
$search->setCache($cache);
$search->setRateLimit(maxRequests: 10, perSeconds: 60);

$start = microtime(true);
$search->search('PHP tutorial', numResults: 3);
$time1 = round((microtime(true) - $start) * 1000);

echo "Waktu: {$time1}ms\n";
echo "Cache Hit: " . ($search->isCacheHit() ? 'Ya' : 'Tidak') . "\n";
echo "Hasil: " . $search->count() . " item\n";

if ($search->hasError()) {
    echo "Error: " . $search->getError() . "\n";
} else {
    foreach ($search->data() as $i => $result) {
        echo ($i + 1) . ". " . $result['title'] . "\n";
    }
}

echo "\n--- Test 2: Pencarian Kedua (dari cache) ---\n";
$search2 = new BraveSearch();
$search2->setCache($cache);
$search2->setRateLimit(maxRequests: 10, perSeconds: 60);

$start = microtime(true);
$search2->search('PHP tutorial', numResults: 3);
$time2 = round((microtime(true) - $start) * 1000);

echo "Waktu: {$time2}ms\n";
echo "Cache Hit: " . ($search2->isCacheHit() ? 'Ya' : 'Tidak') . "\n";
echo "Hasil: " . $search2->count() . " item\n";

if (!$search2->hasError()) {
    foreach ($search2->data() as $i => $result) {
        echo ($i + 1) . ". " . $result['title'] . "\n";
    }
}

echo "\n--- Test 3: Status Rate Limit ---\n";
$status = $search2->getRateLimitStatus();
echo "Rate Limit Enabled: " . ($status['enabled'] ? 'Ya' : 'Tidak') . "\n";
echo "Request Dibuat: " . $status['requests_made'] . "\n";
echo "Request Tersisa: " . $status['requests_remaining'] . "\n";
echo "Reset Dalam: " . $status['reset_in'] . " detik\n";

echo "\n--- Test 4: Pencarian dengan Cache Dimatikan ---\n";
$search3 = new BraveSearch();
$search3->setCache($cache);
$search3->disableCache();
$search3->setRateLimit(maxRequests: 10, perSeconds: 60);

$start = microtime(true);
$search3->search('PHP tutorial', numResults: 3);
$time3 = round((microtime(true) - $start) * 1000);

echo "Waktu: {$time3}ms\n";
echo "Cache Hit: " . ($search3->isCacheHit() ? 'Ya' : 'Tidak') . "\n";
echo "Hasil: " . $search3->count() . " item\n";

echo "\n--- Perbandingan Waktu ---\n";
echo "Tanpa Cache: {$time1}ms\n";
echo "Dengan Cache: {$time2}ms\n";
echo "Cache Dimatikan: {$time3}ms\n";
echo "Penghematan: " . round((1 - $time2/$time1) * 100) . "%\n";

echo "\n=== TEST SELESAI ===\n";
