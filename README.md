# Search Engine Library for PHP

Search multiple engines (Google, Bing, DuckDuckGo, Mojeek, Yahoo) using simple OOP interface.

## Installation

```
composer require developerxnoxs/multi-search-engine
```

## Usage

```php
use SearchEngine\GoogleSearch;

$search = new GoogleSearch();
$result = $search->search('example')->data();
print_r($result);
```
