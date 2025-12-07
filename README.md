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

| Mesin Pencari | Class | Status | Catatan |
|---------------|-------|--------|---------|
| DuckDuckGo | `DuckDuckGoSearch` | Stabil | Rekomendasi utama |
| Yahoo | `YahooSearch` | Stabil | Berfungsi baik |
| Mojeek | `MojeekSearch` | Stabil | Berfungsi baik |
| Bing | `BingSearch` | Rate Limited | Butuh proxy |
| Google | `GoogleSearch` | Rate Limited | Butuh proxy |
| Brave | `BraveSearch` | Rate Limited | Butuh proxy |

> **Penting:** 
> - **DuckDuckGo, Yahoo, dan Mojeek** adalah mesin pencari yang paling stabil dan direkomendasikan.
> - **Bing, Google, dan Brave** memiliki proteksi bot yang ketat (rate limiting, captcha). Untuk menggunakan mesin pencari ini, Anda **memerlukan proxy** atau rotating IP.

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

---

## Lisensi

MIT License - Silakan gunakan untuk proyek pribadi maupun komersial.

## Kontribusi

Pull request sangat diterima! Untuk perubahan besar, silakan buka issue terlebih dahulu.

## Author

developerxnoxs - [developerxnoxs@gmail.com](mailto:developerxnoxs@gmail.com)
