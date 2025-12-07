<?php
require __DIR__ . '/../vendor/autoload.php';

use SearchEngine\GoogleSearch;
use SearchEngine\BingSearch;
use SearchEngine\DuckDuckGoSearch;
use SearchEngine\MojeekSearch;
use SearchEngine\YahooSearch;
use SearchEngine\BraveSearch;

$query = $_GET['q'] ?? '';
$engine = $_GET['engine'] ?? 'google';
$numResults = (int)($_GET['num'] ?? 10);
$results = [];
$error = '';

if ($query) {
    try {
        switch ($engine) {
            case 'google':
                $search = new GoogleSearch();
                break;
            case 'bing':
                $search = new BingSearch();
                break;
            case 'duckduckgo':
                $search = new DuckDuckGoSearch();
                break;
            case 'mojeek':
                $search = new MojeekSearch();
                break;
            case 'yahoo':
                $search = new YahooSearch();
                break;
            case 'brave':
                $search = new BraveSearch();
                break;
            default:
                $search = new GoogleSearch();
        }
        
        $results = $search->search($query, $numResults)->data();
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multi Search Engine - PHP Library Demo</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .search-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .input-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        input[type="text"] {
            flex: 1;
            min-width: 200px;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
        }
        
        select {
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 16px;
            background: white;
            cursor: pointer;
        }
        
        button {
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        button:hover {
            transform: translateY(-2px);
        }
        
        .results {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .result-item {
            padding: 20px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .result-item:last-child {
            border-bottom: none;
        }
        
        .result-title {
            font-size: 18px;
            margin-bottom: 8px;
        }
        
        .result-title a {
            color: #1a0dab;
            text-decoration: none;
        }
        
        .result-title a:hover {
            text-decoration: underline;
        }
        
        .result-url {
            color: #006621;
            font-size: 14px;
            margin-bottom: 5px;
            word-break: break-all;
        }
        
        .result-description {
            color: #545454;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .no-results {
            text-align: center;
            color: #666;
            padding: 40px;
        }
        
        .error {
            background: #fee;
            color: #c33;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .stats {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Multi Search Engine Library</h1>
            <p class="subtitle">PHP library for searching across Google, Bing, DuckDuckGo, Mojeek, Yahoo, and Brave</p>
            
            <form class="search-form" method="GET">
                <div class="input-group">
                    <input type="text" name="q" placeholder="Enter your search query..." value="<?= htmlspecialchars($query) ?>" required>
                    <select name="engine">
                        <option value="google" <?= $engine === 'google' ? 'selected' : '' ?>>Google</option>
                        <option value="bing" <?= $engine === 'bing' ? 'selected' : '' ?>>Bing</option>
                        <option value="duckduckgo" <?= $engine === 'duckduckgo' ? 'selected' : '' ?>>DuckDuckGo</option>
                        <option value="mojeek" <?= $engine === 'mojeek' ? 'selected' : '' ?>>Mojeek</option>
                        <option value="yahoo" <?= $engine === 'yahoo' ? 'selected' : '' ?>>Yahoo</option>
                        <option value="brave" <?= $engine === 'brave' ? 'selected' : '' ?>>Brave</option>
                    </select>
                    <select name="num">
                        <option value="5" <?= $numResults === 5 ? 'selected' : '' ?>>5 results</option>
                        <option value="10" <?= $numResults === 10 ? 'selected' : '' ?>>10 results</option>
                        <option value="20" <?= $numResults === 20 ? 'selected' : '' ?>>20 results</option>
                    </select>
                </div>
                <button type="submit">Search</button>
            </form>
        </div>
        
        <?php if ($error): ?>
            <div class="results">
                <div class="error"><?= htmlspecialchars($error) ?></div>
            </div>
        <?php elseif ($query): ?>
            <div class="results">
                <?php if (count($results) > 0): ?>
                    <div class="stats">Found <?= count($results) ?> results for "<?= htmlspecialchars($query) ?>" on <?= ucfirst($engine) ?></div>
                    <?php foreach ($results as $result): ?>
                        <div class="result-item">
                            <div class="result-title">
                                <a href="<?= htmlspecialchars($result['url']) ?>" target="_blank">
                                    <?= htmlspecialchars($result['title']) ?>
                                </a>
                            </div>
                            <div class="result-url"><?= htmlspecialchars($result['url']) ?></div>
                            <div class="result-description"><?= htmlspecialchars($result['description']) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-results">
                        No results found for "<?= htmlspecialchars($query) ?>". Try a different search term.
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
