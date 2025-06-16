<?php
namespace SearchEngine;

abstract class SearchEngineBase
{
    protected array $results = [];

    abstract protected function buildUrl(string $query, int $start, int $numResults, string $lang, ?string $region): string;
    abstract protected function parseResults(string $html, int $numResults, int &$fetched): void;

    public function search(
        string $query,
        int $numResults = 10,
        string $lang = "en",
        ?string $proxy = null,
        string $safe = "active",
        int $timeout = 5,
        bool $sslVerify = false,
        ?string $region = null,
        int $startNum = 0
    ): self {
        $fetched = 0;
        $start = $startNum;

        while ($fetched < $numResults) {
            $url = $this->buildUrl($query, $start, $numResults, $lang, $region);

            $headers = [
                "User-Agent: {$this->getRandomUserAgent()}",
                "Accept: */*"
            ];

            $opts = [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_SSL_VERIFYPEER => $sslVerify
            ];

            if ($proxy) {
                $opts[CURLOPT_PROXY] = $proxy;
            }

            if (static::class === \SearchEngine\GoogleSearch::class) {
                $opts[CURLOPT_COOKIE] = "CONSENT=PENDING+987; SOCS=CAESHAgBEhIaAB";
            }

            $ch = curl_init();
            curl_setopt_array($ch, $opts);
            $html = curl_exec($ch);
            curl_close($ch);

            if (!$html) break;

            $this->parseResults($html, $numResults, $fetched);
            $start += 10;
        }

        return $this;
    }

    public function url(): array
    {
        return array_column($this->results, 'url');
    }

    public function data(): array
    {
        return $this->results;
    }

    public function json(): string
    {
        return json_encode($this->results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected function getRandomUserAgent(): string
    {
        return sprintf(
            "Lynx/%d.%d.%d libwww-FM/%d.%d SSL-MM/%d.%d OpenSSL/%d.%d.%d",
            rand(2, 3), rand(8, 9), rand(0, 2),
            rand(2, 3), rand(13, 15),
            rand(1, 2), rand(3, 5),
            rand(1, 3), rand(0, 4), rand(0, 9)
        );
    }
}
