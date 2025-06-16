<?php
namespace SearchEngine;

use DOMDocument;
use DOMXPath;

class MojeekSearch extends SearchEngineBase
{
    protected function buildUrl(string $query, int $start, int $numResults, string $lang, ?string $region): string
    {
        return "https://www.mojeek.com/search?" . http_build_query([
            'q' => $query,
            's' => $start
        ]);
    }

    protected function parseResults(string $html, int $numResults, int &$fetched): void
    {
        $doc = new DOMDocument();
        @$doc->loadHTML($html);
        $xpath = new DOMXPath($doc);
        $blocks = $xpath->query('//div[contains(@class, "result")]');

        foreach ($blocks as $block) {
            $a = $xpath->query('.//a', $block)->item(0);
            $desc = $xpath->query('.//p', $block)->item(0);

            if (!$a) continue;

            $this->results[] = [
                'url' => $a->getAttribute('href'),
                'title' => trim($a->textContent),
                'description' => $desc ? trim($desc->textContent) : ''
            ];

            $fetched++;
            if ($fetched >= $numResults) break;
        }
    }
}
