<?php

require __DIR__ . '/../vendor/autoload.php';

use SearchEngine\GoogleSearch;

$google = new GoogleSearch();
$results = $google->search('inurl:/news.php?id=', 5)->data();

print_r($results);
