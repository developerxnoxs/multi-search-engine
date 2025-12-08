<?php
/**
 * Example: Google Search with ScraperAPI
 * 
 * ScraperAPI helps bypass captcha and blocks from search engines.
 * Get your API key at: https://www.scraperapi.com
 */

require __DIR__ . '/../vendor/autoload.php';

use SearchEngine\GoogleSearch;

$apiKey = getenv('SCRAPERAPI_KEY') ?: 'YOUR_SCRAPERAPI_KEY';

echo "=== Google Search with ScraperAPI ===\n\n";

$google = new GoogleSearch();

$google->setScraperApi($apiKey, true);

$results = $google->search('PHP tutorial', 5);

if ($google->hasError()) {
    echo "Error: " . $google->getError() . "\n";
    exit(1);
}

echo "Found " . $google->count() . " results:\n\n";

foreach ($results->data() as $i => $result) {
    echo ($i + 1) . ". " . $result['title'] . "\n";
    echo "   URL: " . $result['url'] . "\n";
    echo "   Desc: " . substr($result['description'], 0, 100) . "...\n\n";
}

echo "HTTP Code: " . $google->getHttpCode() . "\n";
