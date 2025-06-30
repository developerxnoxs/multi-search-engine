<?php
namespace SearchEngine;

use DOMDocument;
use DOMXPath;

class GoogleSearch extends SearchEngineBase
{
    protected function buildUrl(string $query, int $start, int $numResults, string $lang, ?string $region): string
    {
        return "https://www.google.com/search?" . http_build_query([
            'q'    => $query,
            'num'  => $numResults + 2,
            'hl'   => $lang,
            'start'=> $start,
            'safe' => 'active',
            'gl'   => $region
        ]);
    }

    protected function parseResults(string $html, int $numResults, int &$fetched): void
    {
        $doc = new DOMDocument();
        @$doc->loadHTML($html);
        $xpath = new DOMXPath($doc);
        $blocks = $xpath->query('//div[contains(@class,"ezO2md")]');

        foreach ($blocks as $block) {
            $a     = $xpath->query('.//a[@href]', $block)->item(0);
            $title = $xpath->query('.//a//span[contains(@class,"CVA68e")]', $block)->item(0);
            $desc  = $xpath->query('.//span[contains(@class,"FrIlee")]', $block)->item(0);

            if (!$a || !$title || !$desc) continue;

            $href = $a->getAttribute('href');
            if (strpos($href, '/url?') !== 0) continue;
            
            $link = urldecode(preg_replace('/^\/url\?q=/', '', explode('&', $href)[0]));

            if (!filter_var($link, FILTER_VALIDATE_URL)) continue;

            $this->results[] = [
                'url'         => $link,
                'title'       => $title->textContent,
                'description' => $desc->textContent
            ];

            $fetched++;
            if ($fetched >= $numResults) break;
        }
    }
}
