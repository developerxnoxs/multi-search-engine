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
            $title = trim($a->textContent);

            $descNode = $xpath->query('../following-sibling::div[contains(@class,"result__snippet")]', $a)->item(0);
            $description = $descNode ? trim($descNode->textContent) : '';

            $this->results[] = [
                'url' => $href,
                'title' => $title,
                'description' => $description
            ];

            $fetched++;
            if ($fetched >= $numResults) break;
        }
    }
}
