<?php
namespace SearchEngine;

use DOMDocument;
use DOMXPath;

class YahooSearch extends SearchEngineBase
{
    protected function buildUrl(string $query, int $start, int $numResults, string $lang, ?string $region): string
    {
        return "https://search.yahoo.com/search?" . http_build_query([
            'p' => $query,
            'b' => $start + 1,
        ]);
    }

    protected function parseResults(string $html, int $numResults, int &$fetched): void
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        $nodes = $xpath->query("//div[contains(@class,'algo')]");

        foreach ($nodes as $node) {
            $linkNode = $xpath->query(".//a", $node)->item(0);
            $titleNode = $xpath->query(".//h3", $node)->item(0);
            $descNode = $xpath->query(".//p", $node)->item(0);

            if ($linkNode && $titleNode) {
                $href = $linkNode->getAttribute('href');
                $url = $this->extractRealUrl($href);
                $title = trim($titleNode->textContent);
                $desc = $descNode ? trim($descNode->textContent) : '';

                $this->results[] = [
                    'url' => $url,
                    'title' => $title,
                    'description' => $desc,
                ];

                $fetched++;
                if ($fetched >= $numResults) break;
            }
        }
    }

    protected function extractRealUrl(string $yahooRedirect): string
    {
        if (preg_match('/RU=(.*?)\/RK=/', $yahooRedirect, $match)) {
            return urldecode($match[1]);
        }
        return $yahooRedirect;
    }
}
