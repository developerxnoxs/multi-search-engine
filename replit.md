# Multi Search Engine PHP Library

## Overview
Library PHP untuk melakukan pencarian web di berbagai mesin pencari (Google, Bing, DuckDuckGo, Mojeek, Yahoo, Brave). Library ini menyediakan interface OOP yang sederhana untuk scraping hasil pencarian.

## Project Structure
- `src/` - Core library classes
  - `SearchEngineBase.php` - Abstract base class dengan fitur: retry, error handling, filter, dll
  - `GoogleSearch.php`, `BingSearch.php`, `DuckDuckGoSearch.php`, dll - Implementasi tiap mesin pencari
- `example/` - Contoh penggunaan
  - `test.php` - Contoh CLI sederhana
- `vendor/` - Composer dependencies
- `test_all_engines.php` - Script untuk menguji semua mesin pencari

## Search Engine Status
| Engine | Tanpa Proxy | Dengan ScraperAPI | Rekomendasi |
|--------|-------------|-------------------|-------------|
| DuckDuckGo | ✅ Stabil | ✅ Stabil | Langsung tanpa proxy |
| Yahoo | ✅ Stabil | ✅ Stabil | Langsung tanpa proxy |
| Mojeek | ✅ Stabil | ✅ Stabil | Langsung tanpa proxy |
| Bing | ✅ Stabil | ❌ Timeout | Langsung tanpa proxy |
| Google | ❌ Blocked | ✅ OK | Wajib ScraperAPI |
| Brave | ❌ Captcha | ❌ Captcha | Perlu investigasi |

## Recent Changes
- 2024-12-08: Library update
  - Menghapus method `visit()` yang tidak reliable
  - Menghapus parameter `$safe` yang tidak digunakan
  - Menambah User-Agent modern (Chrome/Firefox)
  - Menambah fitur: delay, getHttpCode(), hasError(), getError()
  - Menambah fitur: count(), first(), last(), get(), toArray(), isEmpty()
  - Menambah fitur: map(), each(), setRetry()
  - Menambah retry mechanism dengan exponential backoff
  - Update README.md dengan dokumentasi lengkap dalam Bahasa Indonesia
  - Menghapus frontend (ini adalah library, bukan aplikasi web)

## Library Features
- **Retry mechanism** - Auto retry dengan exponential backoff
- **Error handling** - hasError(), getError(), getHttpCode()
- **Result manipulation** - first(), last(), get(), count(), isEmpty()
- **Filtering** - filterByDomain(), filter() dengan callback
- **Data transformation** - map(), each(), json(), toArray()
- **Proxy support** - Parameter proxy untuk menghindari rate limit

## Usage Example
```php
require __DIR__ . '/vendor/autoload.php';

use SearchEngine\DuckDuckGoSearch;

$search = new DuckDuckGoSearch();
$search->setRetry(3, 1000);

$search->search('PHP programming', numResults: 5);

if ($search->hasError()) {
    echo "Error: " . $search->getError();
} else {
    foreach ($search->data() as $result) {
        echo $result['title'] . "\n";
    }
}
```

## Architecture
- **Type**: PHP Library (no web interface)
- **Dependencies**: Built-in PHP extensions (DOM, cURL)
- **PHP Version**: 8.0+
