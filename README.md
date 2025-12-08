# Multi Search Engine Library untuk PHP

Library PHP untuk melakukan pencarian di berbagai mesin pencari (Google, Bing, DuckDuckGo, Yahoo, Mojeek, Brave) dengan interface OOP yang sederhana.

## Daftar Isi

- [Instalasi](#instalasi)
- [Mesin Pencari yang Tersedia](#mesin-pencari-yang-tersedia)
- [Penggunaan Dasar](#penggunaan-dasar)
- [Parameter Pencarian](#parameter-pencarian)
- [Mengambil Hasil](#mengambil-hasil)
- [Filter Hasil](#filter-hasil)
- [Penanganan Error](#penanganan-error)
- [Penggunaan Proxy](#penggunaan-proxy)
- [ScraperAPI Integration](#scraperapi-integration)
- [Caching](#caching)
- [Rate Limiting](#rate-limiting)
- [Contoh Lengkap](#contoh-lengkap)
- [API Reference](#api-reference)

---

## Instalasi

### Menggunakan Composer

```bash
composer require developerxnoxs/multi-search-engine
```

### Instalasi Manual

1. Download atau clone repository ini
2. Pastikan PHP 8.0+ terinstall
3. Include autoloader:

```php
require __DIR__ . '/vendor/autoload.php';
```

---

## Mesin Pencari yang Tersedia

| Mesin Pencari | Class | Tanpa Proxy | Dengan ScraperAPI | Rekomendasi |
|---------------|-------|-------------|-------------------|-------------|
| DuckDuckGo | `DuckDuckGoSearch` | ✅ Stabil | ✅ Stabil | Langsung tanpa proxy |
| Yahoo | `YahooSearch` | ✅ Stabil | ✅ Stabil | Langsung tanpa proxy |
| Mojeek | `MojeekSearch` | ✅ Stabil | ✅ Stabil | Langsung tanpa proxy |
| Bing | `BingSearch` | ✅ Stabil | ❌ Timeout | Langsung tanpa proxy |
| Google | `GoogleSearch` | ❌ Blocked | ✅ OK | Wajib ScraperAPI |
| Brave | `BraveSearch` | ✅ Stabil | ✅ Stabil | Langsung tanpa proxy |

> **Penting:** 
> - **DuckDuckGo, Yahoo, Mojeek, Bing, dan Brave** berfungsi dengan baik tanpa proxy.
> - **Google** memerlukan [ScraperAPI](https://www.scraperapi.com/) untuk bypass proteksi bot.

---

## Penggunaan Dasar

### Langkah 1: Include Autoloader

```php
<?php
require __DIR__ . '/vendor/autoload.php';
```

### Langkah 2: Import Class yang Dibutuhkan

```php
use SearchEngine\GoogleSearch;
use SearchEngine\BingSearch;
use SearchEngine\DuckDuckGoSearch;
use SearchEngine\YahooSearch;
use SearchEngine\MojeekSearch;
use SearchEngine\BraveSearch;
```

### Langkah 3: Buat Instance dan Cari

```php
// Membuat instance mesin pencari
$bing = new BingSearch();

// Melakukan pencarian
$results = $bing->search('tutorial PHP')->data();

// Menampilkan hasil
print_r($results);
```

### Contoh Output

```php
Array
(
    [0] => Array
        (
            [url] => https://www.php.net/manual/en/tutorial.php
            [title] => PHP: A simple tutorial - Manual
            [description] => Here we would like to show the very basics of PHP...
        )
    [1] => Array
        (
            [url] => https://www.w3schools.com/php/
            [title] => PHP Tutorial - W3Schools
            [description] => PHP is a server scripting language...
        )
)
```

---

## Parameter Pencarian

Method `search()` menerima parameter berikut:

```php
$search->search(
    query: 'kata kunci',      // (wajib) Kata kunci pencarian
    numResults: 10,            // (opsional) Jumlah hasil, default: 10
    lang: 'id',                // (opsional) Kode bahasa, default: 'en'
    proxy: null,               // (opsional) URL proxy
    timeout: 10,               // (opsional) Timeout dalam detik, default: 10
    sslVerify: false,          // (opsional) Verifikasi SSL, default: false
    region: null,              // (opsional) Kode region (contoh: 'id', 'us')
    startNum: 0,               // (opsional) Mulai dari hasil ke-n, default: 0
    delay: 0                   // (opsional) Jeda antar request dalam ms, default: 0
);
```

### Contoh dengan Parameter

```php
$google = new GoogleSearch();

// Pencarian dengan 5 hasil dalam bahasa Indonesia
$results = $google->search('resep masakan', numResults: 5, lang: 'id')->data();

// Pencarian dengan delay 2 detik antar halaman
$results = $google->search('berita hari ini', numResults: 20, delay: 2000)->data();

// Pencarian dengan proxy
$results = $google->search('cuaca', proxy: 'http://proxy.example.com:8080')->data();
```

---

## Mengambil Hasil

Library menyediakan berbagai cara untuk mengambil hasil:

### `data()` atau `toArray()` - Semua Hasil

```php
$search = new BingSearch();
$search->search('PHP tutorial');

// Mengambil semua hasil sebagai array
$allResults = $search->data();
// atau
$allResults = $search->toArray();
```

### `url()` - Hanya URL

```php
$urls = $search->search('PHP tutorial')->url();
// Output: ['https://example.com/1', 'https://example.com/2', ...]
```

### `json()` - Format JSON

```php
$json = $search->search('PHP tutorial')->json();
// Output: JSON string yang rapi
```

### `first()` - Hasil Pertama

```php
$firstResult = $search->search('PHP tutorial')->first();
// Output: ['url' => '...', 'title' => '...', 'description' => '...']
```

### `last()` - Hasil Terakhir

```php
$lastResult = $search->search('PHP tutorial')->last();
```

### `get($index)` - Hasil Berdasarkan Index

```php
$thirdResult = $search->search('PHP tutorial')->get(2); // Index dimulai dari 0
```

### `count()` - Jumlah Hasil

```php
$total = $search->search('PHP tutorial')->count();
// Output: 10
```

### `isEmpty()` - Cek Hasil Kosong

```php
$search->search('query sangat spesifik');

if ($search->isEmpty()) {
    echo "Tidak ada hasil ditemukan";
} else {
    echo "Ditemukan " . $search->count() . " hasil";
}
```

---

## Filter Hasil

### Filter Berdasarkan Domain

```php
$search = new BingSearch();
$results = $search->search('PHP tutorial')
    ->filterByDomain('github.com')
    ->data();
// Hanya menampilkan hasil dari github.com
```

### Filter Custom

```php
$search = new DuckDuckGoSearch();
$results = $search->search('programming')
    ->filter(function($item) {
        // Hanya hasil yang title-nya mengandung 'PHP'
        return str_contains(strtolower($item['title']), 'php');
    })
    ->data();
```

### Map Hasil

```php
$titles = $search->search('PHP tutorial')
    ->map(function($item) {
        return $item['title'];
    });
// Output: ['Title 1', 'Title 2', ...]
```

### Loop dengan Each

```php
$search->search('PHP tutorial')
    ->each(function($item, $index) {
        echo ($index + 1) . ". " . $item['title'] . "\n";
    });
```

---

## Penanganan Error

### Cek Error

```php
$search = new GoogleSearch();
$search->search('test query');

if ($search->hasError()) {
    echo "Error: " . $search->getError();
} else {
    echo "Berhasil! Ditemukan " . $search->count() . " hasil";
}
```

### Cek HTTP Code

```php
$search = new BingSearch();
$search->search('test');

$httpCode = $search->getHttpCode();

if ($httpCode === 200) {
    echo "Request berhasil";
} elseif ($httpCode === 429) {
    echo "Rate limit tercapai, coba lagi nanti";
} else {
    echo "HTTP Code: " . $httpCode;
}
```

### Konfigurasi Retry

Library secara otomatis melakukan retry jika request gagal. Anda bisa mengkonfigurasinya:

```php
$search = new BingSearch();

// Set 5x retry dengan delay 2 detik antar retry
$search->setRetry(count: 5, delayMs: 2000);

$results = $search->search('test')->data();
```

---

## Penggunaan Proxy

Untuk menghindari rate limit, gunakan proxy:

```php
$search = new GoogleSearch();

// HTTP Proxy
$results = $search->search('test', proxy: 'http://proxy.example.com:8080')->data();

// SOCKS5 Proxy
$results = $search->search('test', proxy: 'socks5://proxy.example.com:1080')->data();

// Proxy dengan autentikasi
$results = $search->search('test', proxy: 'http://user:pass@proxy.example.com:8080')->data();
```

---

## ScraperAPI Integration

Library ini mendukung [ScraperAPI](https://www.scraperapi.com/) untuk mengatasi pemblokiran dari mesin pencari. ScraperAPI adalah layanan proxy yang menangani rotasi IP, CAPTCHA, dan rate limiting secara otomatis.

### Cara Mendapatkan API Key

1. Daftar akun gratis di [scraperapi.com](https://www.scraperapi.com/)
2. Salin API key dari dashboard
3. Akun gratis mendapat 5,000 kredit per bulan

### Menggunakan ScraperAPI

#### Mode Auto-Fallback (Rekomendasi)

Dalam mode ini, library akan menggunakan ScraperAPI **hanya ketika** request diblokir (captcha, rate limit, dll):

```php
$search = new GoogleSearch();

// Set API key - ScraperAPI akan digunakan otomatis saat diblokir
$search->setScraperApi('YOUR_SCRAPER_API_KEY');

$results = $search->search('test query')->data();

// Cek apakah ScraperAPI digunakan
if ($search->isUsingScraperApi()) {
    echo "Request menggunakan ScraperAPI";
}
```

#### Mode Always-On

Gunakan ScraperAPI untuk semua request (lebih stabil tapi menggunakan lebih banyak kredit):

```php
$search = new GoogleSearch();

// Parameter kedua = true berarti selalu gunakan ScraperAPI
$search->setScraperApi('YOUR_SCRAPER_API_KEY', useAlways: true);

$results = $search->search('test query')->data();
```

#### Menonaktifkan Auto-Fallback

Jika Anda ingin mengontrol sendiri kapan menggunakan ScraperAPI:

```php
$search = new GoogleSearch();
$search->setScraperApi('YOUR_SCRAPER_API_KEY');

// Nonaktifkan auto-fallback
$search->setAutoFallback(false);

// Request normal tanpa ScraperAPI
$search->search('query 1');

// Aktifkan ScraperAPI untuk request tertentu
$search->setScraperApi('YOUR_SCRAPER_API_KEY', useAlways: true);
$search->search('query 2');
```

### Contoh Lengkap dengan ScraperAPI

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use SearchEngine\GoogleSearch;

// Ambil API key dari environment variable (lebih aman)
$apiKey = getenv('SCRAPER_API_KEY');

$search = new GoogleSearch();
$search->setScraperApi($apiKey);
$search->setRetry(3, 2000);

$search->search('PHP programming tutorial', numResults: 10);

if ($search->hasError()) {
    echo "Error: " . $search->getError() . "\n";
} else {
    echo "Ditemukan " . $search->count() . " hasil:\n\n";
    
    foreach ($search->data() as $i => $result) {
        echo ($i + 1) . ". " . $result['title'] . "\n";
        echo "   " . $result['url'] . "\n\n";
    }
}
```

### Tips Penggunaan ScraperAPI

1. **Simpan API key di environment variable** - Jangan hardcode di source code
2. **Gunakan mode auto-fallback** - Hemat kredit dengan hanya menggunakan ScraperAPI saat diperlukan
3. **Tambahkan delay** - Meskipun menggunakan ScraperAPI, delay tetap membantu menghindari rate limit
4. **Timeout lebih lama** - ScraperAPI memerlukan waktu lebih lama, library otomatis menggunakan timeout minimal 60 detik

---

## Caching

Library mendukung caching untuk menghindari request berulang dengan query yang sama.

### Menggunakan FileCache

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use SearchEngine\BingSearch;
use SearchEngine\FileCache;

// Buat cache dengan TTL 1 jam
$cache = new FileCache('/tmp/search_cache', 3600);

$search = new BingSearch();
$search->setCache($cache);

// Request pertama - dari internet
$search->search('PHP tutorial');
echo $search->isCacheHit() ? "Dari cache" : "Dari internet"; // Dari internet

// Request kedua - dari cache (sangat cepat!)
$search->search('PHP tutorial');
echo $search->isCacheHit() ? "Dari cache" : "Dari internet"; // Dari cache
```

### Kontrol Cache

```php
// Nonaktifkan cache sementara
$search->disableCache();
$search->search('query baru'); // Selalu request baru

// Aktifkan kembali
$search->enableCache();

// Hapus semua cache
$search->clearCache();
```

---

## Rate Limiting

Lindungi dari rate limit dengan membatasi jumlah request per waktu.

### Menggunakan Rate Limiter

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use SearchEngine\GoogleSearch;

$search = new GoogleSearch();

// Batasi 10 request per 60 detik dengan auto-backoff
$search->setRateLimit(maxRequests: 10, perSeconds: 60, autoBackoff: true);

// Library akan otomatis menunggu jika limit tercapai
for ($i = 0; $i < 20; $i++) {
    $search->search("query $i");
    echo "Request $i selesai\n";
}
```

### Cek Status Rate Limit

```php
$status = $search->getRateLimitStatus();

echo "Request dibuat: " . $status['requests_made'] . "\n";
echo "Request tersisa: " . $status['requests_remaining'] . "\n";
echo "Reset dalam: " . $status['reset_in'] . " detik\n";
echo "Backoff aktif: " . ($status['backoff_active'] ? 'Ya' : 'Tidak') . "\n";
```

### Auto-Backoff

Jika request gagal (rate limit, error), library akan otomatis menambah delay secara eksponensial sebelum retry berikutnya.

---

## Contoh Lengkap

### Contoh 1: Pencarian Sederhana

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use SearchEngine\BingSearch;

$search = new BingSearch();
$results = $search->search('belajar PHP untuk pemula', numResults: 5)->data();

foreach ($results as $index => $result) {
    echo ($index + 1) . ". " . $result['title'] . "\n";
    echo "   URL: " . $result['url'] . "\n";
    echo "   " . $result['description'] . "\n\n";
}
```

### Contoh 2: Mencari di Beberapa Mesin Pencari

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use SearchEngine\BingSearch;
use SearchEngine\DuckDuckGoSearch;
use SearchEngine\YahooSearch;

$query = 'framework PHP terbaik 2024';
$engines = [
    'Bing' => new BingSearch(),
    'DuckDuckGo' => new DuckDuckGoSearch(),
    'Yahoo' => new YahooSearch(),
];

foreach ($engines as $name => $engine) {
    echo "=== Hasil dari $name ===\n";
    
    $results = $engine->search($query, numResults: 3)->data();
    
    if (empty($results)) {
        echo "Tidak ada hasil\n";
    } else {
        foreach ($results as $result) {
            echo "- " . $result['title'] . "\n";
        }
    }
    echo "\n";
}
```

### Contoh 3: Dengan Error Handling

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use SearchEngine\GoogleSearch;

$search = new GoogleSearch();
$search->setRetry(3, 1000); // 3x retry, delay 1 detik

$search->search('test query', delay: 500);

if ($search->hasError()) {
    echo "Pencarian gagal: " . $search->getError() . "\n";
    echo "HTTP Code: " . $search->getHttpCode() . "\n";
} elseif ($search->isEmpty()) {
    echo "Tidak ada hasil ditemukan\n";
} else {
    echo "Ditemukan " . $search->count() . " hasil:\n\n";
    
    $search->each(function($item, $i) {
        echo ($i + 1) . ". " . $item['title'] . "\n";
    });
}
```

### Contoh 4: Export ke JSON

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use SearchEngine\MojeekSearch;

$search = new MojeekSearch();
$json = $search->search('open source projects', numResults: 10)->json();

// Simpan ke file
file_put_contents('hasil-pencarian.json', $json);

echo "Hasil disimpan ke hasil-pencarian.json\n";
```

---

## API Reference

### Method Pencarian

| Method | Return | Deskripsi |
|--------|--------|-----------|
| `search($query, ...)` | `self` | Melakukan pencarian |
| `setRetry($count, $delayMs)` | `self` | Mengatur retry dan delay |

### Method Pengambilan Data

| Method | Return | Deskripsi |
|--------|--------|-----------|
| `data()` | `array` | Semua hasil sebagai array |
| `toArray()` | `array` | Alias untuk `data()` |
| `url()` | `array` | Hanya URL dari semua hasil |
| `json()` | `string` | Semua hasil sebagai JSON |
| `first()` | `?array` | Hasil pertama atau null |
| `last()` | `?array` | Hasil terakhir atau null |
| `get($index)` | `?array` | Hasil pada index tertentu |
| `count()` | `int` | Jumlah hasil |
| `isEmpty()` | `bool` | True jika tidak ada hasil |

### Method Filter

| Method | Return | Deskripsi |
|--------|--------|-----------|
| `filterByDomain($domain)` | `self` | Filter berdasarkan domain |
| `filter($callback)` | `self` | Filter dengan callback custom |
| `map($callback)` | `array` | Transform setiap hasil |
| `each($callback)` | `self` | Loop setiap hasil |

### Method Error Handling

| Method | Return | Deskripsi |
|--------|--------|-----------|
| `hasError()` | `bool` | True jika ada error |
| `getError()` | `?string` | Pesan error atau null |
| `getHttpCode()` | `int` | HTTP status code terakhir |

### Method ScraperAPI

| Method | Return | Deskripsi |
|--------|--------|-----------|
| `setScraperApi($key, $useAlways)` | `self` | Mengatur API key ScraperAPI |
| `setAutoFallback($enabled)` | `self` | Aktifkan/nonaktifkan auto-fallback |
| `isUsingScraperApi()` | `bool` | Cek apakah menggunakan ScraperAPI |

### Method Caching

| Method | Return | Deskripsi |
|--------|--------|-----------|
| `setCache($cache, $ttl)` | `self` | Mengatur cache dengan TTL (default 3600 detik) |
| `enableCache($enabled)` | `self` | Aktifkan/nonaktifkan cache |
| `disableCache()` | `self` | Nonaktifkan cache |
| `isCacheHit()` | `bool` | True jika hasil dari cache |
| `clearCache()` | `bool` | Hapus semua cache |

### Method Rate Limiting

| Method | Return | Deskripsi |
|--------|--------|-----------|
| `setRateLimit($max, $perSeconds, $autoBackoff)` | `self` | Atur rate limit (default 10 req/60 detik) |
| `setRateLimiter($rateLimiter)` | `self` | Gunakan RateLimiter custom |
| `enableRateLimit($enabled)` | `self` | Aktifkan/nonaktifkan rate limit |
| `disableRateLimit()` | `self` | Nonaktifkan rate limit |
| `getRateLimitStatus()` | `array` | Status rate limit saat ini |

---

## Lisensi

MIT License - Silakan gunakan untuk proyek pribadi maupun komersial.

## Kontribusi

Pull request sangat diterima! Untuk perubahan besar, silakan buka issue terlebih dahulu.

## Author

developerxnoxs - [developerxnoxs@gmail.com](mailto:developerxnoxs@gmail.com)
