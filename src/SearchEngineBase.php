<?php
namespace SearchEngine;

use Exception;

abstract class SearchEngineBase
{
    protected array $results = [];
    protected int $httpCode = 0;
    protected ?string $error = null;
    protected int $retryCount = 3;
    protected int $retryDelay = 1000;
    protected ?string $scraperApiKey = null;
    protected bool $useScraperApi = false;
    protected bool $autoFallbackToScraperApi = true;

    abstract protected function buildUrl(string $query, int $start, int $numResults, string $lang, ?string $region): string;
    abstract protected function parseResults(string $html, int $numResults, int &$fetched): void;

    protected function detectBlockedPage(string $html): ?string
    {
        $patterns = [
            '/<[^>]*captcha[^>]*>/i' => 'Captcha detected - please use a proxy',
            '/id=["\']captcha/i' => 'Captcha detected - please use a proxy',
            '/class=["\'][^"\']*captcha[^"\']*["\']/i' => 'Captcha detected - please use a proxy',
            '/solve.*captcha/i' => 'Captcha detected - please use a proxy',
            '/complete.*captcha/i' => 'Captcha detected - please use a proxy',
            '/unusual\s+traffic/i' => 'Unusual traffic detected - please use a proxy',
            '/automated\s+queries/i' => 'Automated queries blocked - please use a proxy',
            '/are\s+you\s+a?\s*robot/i' => 'Bot detection triggered - please use a proxy',
            '/verify\s+you\s+are\s+human/i' => 'Human verification required - please use a proxy',
            '/too\s+many\s+requests/i' => 'Rate limit reached - please use a proxy or add delay',
            '/please\s+complete\s+the\s+security\s+check/i' => 'Security check required - please use a proxy',
        ];

        foreach ($patterns as $pattern => $message) {
            if (preg_match($pattern, $html)) {
                return $message;
            }
        }

        return null;
    }

    public function search(
        string $query,
        int $numResults = 10,
        string $lang = "en",
        ?string $proxy = null,
        int $timeout = 10,
        bool $sslVerify = false,
        ?string $region = null,
        int $startNum = 0,
        int $delay = 0
    ): self {
        $this->results = [];
        $this->error = null;
        $this->httpCode = 0;
        
        $fetched = 0;
        $start = $startNum;
        $useScraperApiForThisRequest = $this->useScraperApi;

        while ($fetched < $numResults) {
            $url = $this->buildUrl($query, $start, $numResults, $lang, $region);

            if ($useScraperApiForThisRequest && $this->scraperApiKey !== null) {
                $url = $this->buildScraperApiUrl($url);
            }

            $headers = [
                "User-Agent: {$this->getRandomUserAgent()}",
                "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
                "Accept-Language: {$lang},en-US;q=0.7,en;q=0.3",
                "Accept-Encoding: gzip, deflate",
                "Connection: keep-alive",
                "Upgrade-Insecure-Requests: 1"
            ];

            $opts = [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => $useScraperApiForThisRequest ? max($timeout, 60) : $timeout,
                CURLOPT_SSL_VERIFYPEER => $sslVerify,
                CURLOPT_ENCODING => ''
            ];

            if ($proxy && !$useScraperApiForThisRequest) {
                $opts[CURLOPT_PROXY] = $proxy;
            }

            if (static::class === \SearchEngine\GoogleSearch::class && !$useScraperApiForThisRequest) {
                $opts[CURLOPT_COOKIE] = "CONSENT=PENDING+987; SOCS=CAESHAgBEhIaAB";
            }

            $html = $this->executeWithRetry($opts);
            
            if ($html === false) {
                if ($this->canFallbackToScraperApi() && !$useScraperApiForThisRequest) {
                    $useScraperApiForThisRequest = true;
                    $this->error = null;
                    continue;
                }
                break;
            }

            $blockedMessage = $this->detectBlockedPage($html);
            if ($blockedMessage !== null) {
                if ($this->canFallbackToScraperApi() && !$useScraperApiForThisRequest) {
                    $useScraperApiForThisRequest = true;
                    $this->error = null;
                    continue;
                }
                $this->error = $blockedMessage;
                break;
            }

            $previousCount = count($this->results);
            $this->parseResults($html, $numResults, $fetched);
            
            if (count($this->results) === $previousCount) {
                break;
            }
            
            $start += 10;

            if ($delay > 0 && $fetched < $numResults) {
                usleep($delay * 1000);
            }
        }

        return $this;
    }

    protected function canFallbackToScraperApi(): bool
    {
        return $this->autoFallbackToScraperApi && $this->scraperApiKey !== null;
    }

    protected function executeWithRetry(array $opts): string|false
    {
        $attempt = 0;
        $lastError = null;

        while ($attempt < $this->retryCount) {
            $ch = curl_init();
            curl_setopt_array($ch, $opts);
            $html = curl_exec($ch);
            $this->httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($html !== false && $this->httpCode >= 200 && $this->httpCode < 400) {
                return $html;
            }

            $lastError = $curlError ?: "HTTP {$this->httpCode}";
            $attempt++;

            if ($attempt < $this->retryCount) {
                usleep($this->retryDelay * 1000 * (int)pow(2, $attempt - 1));
            }
        }

        $this->error = "Request failed after {$this->retryCount} attempts: {$lastError}";
        return false;
    }

    public function setRetry(int $count, int $delayMs = 1000): self
    {
        $this->retryCount = max(1, $count);
        $this->retryDelay = max(0, $delayMs);
        return $this;
    }

    public function setScraperApi(?string $apiKey, bool $useAlways = false): self
    {
        $this->scraperApiKey = $apiKey;
        $this->useScraperApi = $useAlways;
        return $this;
    }

    public function setAutoFallback(bool $enabled): self
    {
        $this->autoFallbackToScraperApi = $enabled;
        return $this;
    }

    public function isUsingScraperApi(): bool
    {
        return $this->useScraperApi;
    }

    protected function buildScraperApiUrl(string $url): string
    {
        if ($this->scraperApiKey === null) {
            return $url;
        }
        
        return 'http://api.scraperapi.com?' . http_build_query([
            'api_key' => $this->scraperApiKey,
            'url' => $url,
            'render' => 'false'
        ]);
    }

    public function url(): array
    {
        return array_column($this->results, 'url');
    }

    public function data(): array
    {
        return $this->results;
    }

    public function toArray(): array
    {
        return $this->results;
    }

    public function json(): string
    {
        return json_encode($this->results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function count(): int
    {
        return count($this->results);
    }

    public function first(): ?array
    {
        return $this->results[0] ?? null;
    }

    public function last(): ?array
    {
        if (empty($this->results)) {
            return null;
        }
        return $this->results[count($this->results) - 1];
    }

    public function get(int $index): ?array
    {
        return $this->results[$index] ?? null;
    }

    public function isEmpty(): bool
    {
        return empty($this->results);
    }

    public function hasError(): bool
    {
        return $this->error !== null;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    protected function getRandomUserAgent(): string
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64; rv:121.0) Gecko/20100101 Firefox/121.0'
        ];
        
        return $userAgents[array_rand($userAgents)];
    }

    public function filterByDomain(string $domain): self
    {
        $this->results = array_filter($this->results, function ($item) use ($domain) {
            $host = parse_url($item['url'] ?? '', PHP_URL_HOST);
            return isset($item['url']) && is_string($host) && str_contains($host, $domain);
        });

        $this->results = array_values($this->results);
        return $this;
    }

    public function filter(callable $callback): self
    {
        $this->results = array_filter($this->results, $callback);
        $this->results = array_values($this->results);
        return $this;
    }

    public function map(callable $callback): array
    {
        return array_map($callback, $this->results);
    }

    public function each(callable $callback): self
    {
        foreach ($this->results as $index => $result) {
            $callback($result, $index);
        }
        return $this;
    }
}
