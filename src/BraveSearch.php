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

        $nodes = $xpath->query('//div[contains(@class,"snippet")]');

        foreach ($nodes as $node) {
            $a = $xpath->query('.//a[@href]', $node)->item(0);
            $title = $xpath->query('.//a', $node)->item(0);
            $desc = $xpath->query('.//div[contains(@class,"snippet-description")]', $node)->item(0);

            if (!$a || !$title || !$desc) continue;

            $this->results[] = [
                'url' => $a->getAttribute('href'),
                'title' => trim($title->textContent),
                'description' => trim($desc->textContent),
            ];

            $fetched++;
            if ($fetched >= $numResults) break;
        }
    }
}
