<?php
namespace SearchEngine;

class FileCache implements CacheInterface
{
    private string $cacheDir;
    private int $defaultTtl;

    public function __construct(string $cacheDir = '/tmp/search_cache', int $defaultTtl = 3600)
    {
        $this->cacheDir = rtrim($cacheDir, '/');
        $this->defaultTtl = $defaultTtl;
        
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function get(string $key): ?array
    {
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            return null;
        }

        $content = file_get_contents($file);
        if ($content === false) {
            return null;
        }

        $data = json_decode($content, true);
        if (!is_array($data) || !isset($data['expires'], $data['data'])) {
            return null;
        }

        if (time() > $data['expires']) {
            $this->delete($key);
            return null;
        }

        return $data['data'];
    }

    public function set(string $key, array $data, int $ttl = 0): bool
    {
        $ttl = $ttl > 0 ? $ttl : $this->defaultTtl;
        $file = $this->getFilePath($key);
        
        $content = json_encode([
            'expires' => time() + $ttl,
            'data' => $data
        ], JSON_UNESCAPED_SLASHES);

        return file_put_contents($file, $content) !== false;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function delete(string $key): bool
    {
        $file = $this->getFilePath($key);
        
        if (file_exists($file)) {
            return unlink($file);
        }
        
        return true;
    }

    public function clear(): bool
    {
        $files = glob($this->cacheDir . '/*.cache');
        
        if ($files === false) {
            return false;
        }

        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }

    private function getFilePath(string $key): string
    {
        $hash = md5($key);
        return $this->cacheDir . '/' . $hash . '.cache';
    }

    public static function generateKey(string $engine, string $query, array $params = []): string
    {
        $keyData = [
            'engine' => $engine,
            'query' => strtolower(trim($query)),
            'params' => $params
        ];
        return json_encode($keyData);
    }
}
