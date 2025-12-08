<?php
namespace SearchEngine;

use DOMDocument;
use DOMXPath;

class BraveSearch extends SearchEngineBase
{
    protected function buildUrl(string $query, int $start, int $numResults, string $lang, ?string $region): string
    {
        // Brave menggunakan pagination dengan parameter "offset"
        return "https://search.brave.com/search?" . http_build_query([
            'q' => $query,
            'offset' => $start,
            'count' => $numResults,
            'lang' => $lang
        ]);
    }

    protected function parseResults(string $html, int $numResults, int &$fetched): void
    {
        $doc = new DOMDocument();
        @$doc->loadHTML($html);
        $xpath = new DOMXPath($doc);

        $nodes = $xpath->query('//div[contains(@class,"snippet") and contains(@data-type,"web")]');

        foreach ($nodes as $node) {
            $aNode = $xpath->query('.//a[@href][contains(@class,"svelte")]', $node)->item(0);
            
            $titleNode = $xpath->query('.//div[contains(@class,"title") and contains(@class,"search-snippet-title")]', $node)->item(0);
            if (!$titleNode) {
                $titleNode = $xpath->query('.//div[contains(@class,"title")]', $node)->item(0);
            }
            
            $descNode = $xpath->query('.//div[contains(@class,"content") and contains(@class,"desktop-default-regular")]', $node)->item(0);
            if (!$descNode) {
                $descNode = $xpath->query('.//div[contains(@class,"generic-snippet")]', $node)->item(0);
            }
            if (!$descNode) {
                $descNode = $xpath->query('.//div[contains(@class,"snippet-description")]', $node)->item(0);
            }

            if (!$aNode instanceof \DOMElement) continue;
            
            $url = $aNode->getAttribute('href');
            if (empty($url) || str_starts_with($url, '#') || str_contains($url, 'brave.com')) {
                continue;
            }

            $this->results[] = [
                'url' => $url,
                'title' => $titleNode ? trim($titleNode->textContent) : '',
                'description' => $descNode ? trim($descNode->textContent) : '',
            ];

            $fetched++;
            if ($fetched >= $numResults) break;
        }
    }
}
