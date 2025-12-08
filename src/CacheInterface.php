<?php
namespace SearchEngine;

interface CacheInterface
{
    public function get(string $key): ?array;
    public function set(string $key, array $data, int $ttl = 3600): bool;
    public function has(string $key): bool;
    public function delete(string $key): bool;
    public function clear(): bool;
}
