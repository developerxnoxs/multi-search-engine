<?php
namespace SearchEngine;

class RateLimiter
{
    private int $maxRequests;
    private int $perSeconds;
    private string $storageFile;
    private bool $autoBackoff;
    private int $backoffMultiplier;
    private int $currentBackoff;

    public function __construct(
        int $maxRequests = 10,
        int $perSeconds = 60,
        string $storageFile = '/tmp/rate_limiter.json',
        bool $autoBackoff = true
    ) {
        $this->maxRequests = $maxRequests;
        $this->perSeconds = $perSeconds;
        $this->storageFile = $storageFile;
        $this->autoBackoff = $autoBackoff;
        $this->backoffMultiplier = 2;
        $this->currentBackoff = 0;
    }

    public function canProceed(string $identifier = 'default'): bool
    {
        $data = $this->loadData();
        $now = time();
        
        if (!isset($data[$identifier])) {
            $data[$identifier] = ['requests' => [], 'backoff_until' => 0];
        }

        if ($now < $data[$identifier]['backoff_until']) {
            return false;
        }

        $data[$identifier]['requests'] = array_filter(
            $data[$identifier]['requests'],
            fn($timestamp) => $timestamp > ($now - $this->perSeconds)
        );

        $requestCount = count($data[$identifier]['requests']);
        $this->saveData($data);

        return $requestCount < $this->maxRequests;
    }

    public function recordRequest(string $identifier = 'default'): void
    {
        $data = $this->loadData();
        $now = time();
        
        if (!isset($data[$identifier])) {
            $data[$identifier] = ['requests' => [], 'backoff_until' => 0];
        }

        $data[$identifier]['requests'][] = $now;
        $this->currentBackoff = 0;
        
        $this->saveData($data);
    }

    public function recordFailure(string $identifier = 'default'): void
    {
        if (!$this->autoBackoff) {
            return;
        }

        $data = $this->loadData();
        $now = time();
        
        if (!isset($data[$identifier])) {
            $data[$identifier] = ['requests' => [], 'backoff_until' => 0];
        }

        $this->currentBackoff = $this->currentBackoff === 0 
            ? 1 
            : min($this->currentBackoff * $this->backoffMultiplier, 300);

        $data[$identifier]['backoff_until'] = $now + $this->currentBackoff;
        
        $this->saveData($data);
    }

    public function waitIfNeeded(string $identifier = 'default'): int
    {
        $data = $this->loadData();
        $now = time();
        
        if (!isset($data[$identifier])) {
            return 0;
        }

        if ($now < $data[$identifier]['backoff_until']) {
            $waitTime = $data[$identifier]['backoff_until'] - $now;
            sleep($waitTime);
            return $waitTime;
        }

        $data[$identifier]['requests'] = array_filter(
            $data[$identifier]['requests'],
            fn($timestamp) => $timestamp > ($now - $this->perSeconds)
        );

        if (count($data[$identifier]['requests']) >= $this->maxRequests) {
            $oldestRequest = min($data[$identifier]['requests']);
            $waitTime = ($oldestRequest + $this->perSeconds) - $now + 1;
            
            if ($waitTime > 0) {
                sleep($waitTime);
                return $waitTime;
            }
        }

        return 0;
    }

    public function getStatus(string $identifier = 'default'): array
    {
        $data = $this->loadData();
        $now = time();
        
        if (!isset($data[$identifier])) {
            return [
                'requests_made' => 0,
                'requests_remaining' => $this->maxRequests,
                'reset_in' => 0,
                'backoff_active' => false,
                'backoff_remaining' => 0
            ];
        }

        $data[$identifier]['requests'] = array_filter(
            $data[$identifier]['requests'],
            fn($timestamp) => $timestamp > ($now - $this->perSeconds)
        );

        $requestsMade = count($data[$identifier]['requests']);
        $oldestRequest = !empty($data[$identifier]['requests']) 
            ? min($data[$identifier]['requests']) 
            : $now;

        return [
            'requests_made' => $requestsMade,
            'requests_remaining' => max(0, $this->maxRequests - $requestsMade),
            'reset_in' => max(0, ($oldestRequest + $this->perSeconds) - $now),
            'backoff_active' => $now < $data[$identifier]['backoff_until'],
            'backoff_remaining' => max(0, $data[$identifier]['backoff_until'] - $now)
        ];
    }

    public function reset(string $identifier = 'default'): void
    {
        $data = $this->loadData();
        unset($data[$identifier]);
        $this->currentBackoff = 0;
        $this->saveData($data);
    }

    public function resetAll(): void
    {
        $this->currentBackoff = 0;
        file_put_contents($this->storageFile, '{}');
    }

    private function loadData(): array
    {
        if (!file_exists($this->storageFile)) {
            return [];
        }

        $content = file_get_contents($this->storageFile);
        if ($content === false) {
            return [];
        }

        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
    }

    private function saveData(array $data): void
    {
        file_put_contents($this->storageFile, json_encode($data));
    }

    public function setMaxRequests(int $maxRequests): self
    {
        $this->maxRequests = $maxRequests;
        return $this;
    }

    public function setPerSeconds(int $perSeconds): self
    {
        $this->perSeconds = $perSeconds;
        return $this;
    }

    public function setAutoBackoff(bool $enabled): self
    {
        $this->autoBackoff = $enabled;
        return $this;
    }
}
