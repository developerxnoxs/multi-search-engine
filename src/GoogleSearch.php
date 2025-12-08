<?php
namespace SearchEngine;

use DOMDocument;
use DOMXPath;
use DOMElement;

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
        
        $blocks = $xpath->query('//div[contains(@class,"MjjYud")]');
        
        if ($blocks->length === 0) {
            $blocks = $xpath->query('//div[contains(@class,"ezO2md")]');
        }

        foreach ($blocks as $block) {
            $linkContainer = $xpath->query('.//div[contains(@class,"yuRUbf")]//a[@href]', $block)->item(0);
            if (!$linkContainer) {
                $linkContainer = $xpath->query('.//a[@href]', $block)->item(0);
            }
            
            $title = $xpath->query('.//h3[contains(@class,"LC20lb")]', $block)->item(0);
            if (!$title) {
                $title = $xpath->query('.//a//span[contains(@class,"CVA68e")]', $block)->item(0);
            }
            
            $desc = $xpath->query('.//div[contains(@class,"VwiC3b")]', $block)->item(0);
            if (!$desc) {
                $desc = $xpath->query('.//span[contains(@class,"FrIlee")]', $block)->item(0);
            }

            if (!$linkContainer instanceof DOMElement || !$title) continue;

            $href = $linkContainer->getAttribute('href');
            
            if (strpos($href, '/url?') === 0) {
                $link = urldecode(preg_replace('/^\/url\?q=/', '', explode('&', $href)[0]));
            } elseif (strpos($href, 'http') === 0) {
                $link = $href;
            } else {
                continue;
            }

            if (!filter_var($link, FILTER_VALIDATE_URL)) continue;
            
            if (strpos($link, 'google.com') !== false) continue;

            $this->results[] = [
                'url'         => $link,
                'title'       => trim($title->textContent),
                'description' => $desc ? trim($desc->textContent) : ''
            ];

            $fetched++;
            if ($fetched >= $numResults) break;
        }
    }
}
