# Multi Search Engine PHP Library

## Overview
This is a PHP library for performing web searches across multiple search engines (Google, Bing, DuckDuckGo, Mojeek, Yahoo, and Brave). The library provides a simple OOP interface for scraping search results.

## Project Structure
- `src/` - Core library classes
  - `SearchEngineBase.php` - Abstract base class for all search engines
  - `GoogleSearch.php`, `BingSearch.php`, `DuckDuckGoSearch.php`, etc. - Individual search engine implementations
- `public/` - Web demo interface
  - `index.php` - Interactive web interface for testing the library
- `example/` - Example usage scripts
  - `test.php` - CLI example
- `vendor/` - Composer dependencies
- `server.php` - Development server script (runs on port 5000)

## Current State
The project has been set up in the Replit environment with:
- PHP 8.2 installed
- Web demo interface created at `public/index.php`
- Development server configured to run on port 5000 (0.0.0.0:5000)
- Workflow configured to run the PHP development server

## Architecture
This is a library project with a web demo frontend:
- **Backend**: PHP library classes that scrape various search engines using cURL
- **Frontend**: Single-page PHP interface for demonstrating the library's capabilities
- **Dependencies**: No external PHP packages required (uses built-in PHP extensions: DOM, cURL)

## Usage

### Web Interface
The web demo is available at the Replit preview URL. It provides:
- Search form with query input
- Search engine selector (Google, Bing, DuckDuckGo, Mojeek, Yahoo, Brave)
- Results count selector (5, 10, or 20 results)
- Clean, modern UI displaying search results

### Library Usage (CLI)
```php
require __DIR__ . '/vendor/autoload.php';

use SearchEngine\GoogleSearch;

$google = new GoogleSearch();
$results = $google->search('your query', 10)->data();
print_r($results);
```

## Recent Changes
- 2024-12-07: Initial Replit setup
  - Installed PHP 8.2
  - Created web demo interface at `public/index.php`
  - Created development server script `server.php`
  - Configured workflow for PHP Dev Server on port 5000
