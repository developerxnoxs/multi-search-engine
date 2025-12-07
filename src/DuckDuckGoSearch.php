<?php
namespace SearchEngine;

use DOMDocument;
use DOMXPath;

class DuckDuckGoSearch extends SearchEngineBase
{
    protected function buildUrl(string $query, int $start, int $numResults, string $lang, ?string $region): string
    {
        return "https://html.duckduckgo.com/html/?" . http_build_query([
            'q' => $query,
            's' => $start,
            'kl' => $region,
            'lang' => $lang
        ]);
    }

    protected function parseResults(string $html, int $numResults, int &$fetched): void
    {
        $doc = new DOMDocument();
        @$doc->loadHTML($html);
        $xpath = new DOMXPath($doc);

        $nodes = $xpath->query('//a[contains(@class,"result__a")]');

        foreach ($nodes as $a) {
            $href = $a->getAttribute('href');
            $url = $this->extractRealUrl($href);
            
            if ($this->isAdUrl($url) || $this->isAdUrl($href)) {
                continue;
            }
            
            $title = trim($a->textContent);

            $descNode = $xpath->query('../following-sibling::div[contains(@class,"result__snippet")]', $a)->item(0);
            $description = $descNode ? trim($descNode->textContent) : '';

            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }

            $this->results[] = [
                'url' => $url,
                'title' => $title,
                'description' => $description
            ];

            $fetched++;
            if ($fetched >= $numResults) break;
        }
    }

    protected function extractRealUrl(string $ddgRedirect): string
    {
        if (preg_match('/uddg=([^&]+)/', $ddgRedirect, $match)) {
            $decoded = urldecode($match[1]);
            if (preg_match('/^https?:\/\//', $decoded)) {
                return $decoded;
            }
        }

        if (str_starts_with($ddgRedirect, '//')) {
            return 'https:' . $ddgRedirect;
        }

        return $ddgRedirect;
    }

    protected function isAdUrl(string $url): bool
    {
        $adPatterns = [
            'duckduckgo.com/y.js',
            'ad_domain=',
            'ad_provider=',
            'ad_type=',
        ];

        foreach ($adPatterns as $pattern) {
            if (str_contains($url, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
