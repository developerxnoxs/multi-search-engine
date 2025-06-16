# Search Engine Library for PHP

Search multiple engines (Google, Bing, DuckDuckGo, Mojeek, Yahoo) using simple OOP interface.

## Installation

```
composer require developerxnoxs/multi-search-engine
```

## Usage

```php
require __DIR__.'/vendor/autoload.php';

use SearchEngine\GoogleSearch;
use SearchEngine\BingSearch;
use SearchEngine\DuckDuckGoSearch;

$google = new GoogleSearch();
$bing = new BingSearch();
$duck = new DuckDuckGoSearch();

$query = "ChatGPT";

echo "Google:\n";
print_r($google->search($query)->url());
print_r($google->search($query)->json());
print_r($google->search($query)->data());
```
