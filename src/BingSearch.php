<?php
namespace SearchEngine;

use DOMDocument;
use DOMXPath;

class BingSearch extends SearchEngineBase
{
    protected function buildUrl(string $query, int $start, int $numResults, string $lang, ?string $region): string
    {
        return "https://www.bing.com/search?" . http_build_query([
            'q' => $query,
            'first' => $start + 1,
            'count' => $numResults + 2,
            'setlang' => $lang,
            'cc' => $region
        ]);
    }

    protected function parseResults(string $html, int $numResults, int &$fetched): void
    {
        $doc = new DOMDocument();
        @$doc->loadHTML($html);
        $xpath = new DOMXPath($doc);
        $blocks = $xpath->query('//li[@class="b_algo"]');

        foreach ($blocks as $block) {
            $a = $xpath->query('.//h2/a', $block)->item(0);
            $desc = $xpath->query('.//div[@class="b_caption"]/p', $block)->item(0);

            if (!$a || !$desc) continue;

            $this->results[] = [
                'url' => $a->getAttribute('href'),
                'title' => trim($a->textContent),
                'description' => trim($desc->textContent)
            ];

            $fetched++;
            if ($fetched >= $numResults) break;
        }
    }
}
