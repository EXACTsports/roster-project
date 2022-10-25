<?php

namespace App\Spiders;

use Generator;
use RoachPHP\Downloader\Middleware\RequestDeduplicationMiddleware;
use RoachPHP\Extensions\LoggerExtension;
use RoachPHP\Extensions\StatsCollectorExtension;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use RoachPHP\Spider\ParseResult;
use Symfony\Component\DomCrawler\Crawler;

class OpendorseLinkSpider extends BasicSpider
{
    public array $startUrls = [
        //
    ];

    public array $downloaderMiddleware = [
        RequestDeduplicationMiddleware::class,
    ];

    public array $spiderMiddleware = [
        //
    ];

    public array $itemProcessors = [
        //
    ];

    public array $extensions = [
        LoggerExtension::class,
        StatsCollectorExtension::class,
    ];

    public int $concurrency = 2;

    public int $requestDelay = 1;

    /**
     * @return Generator<ParseResult>
     */
    public function parse(Response $response): Generator
    {
        $result = [];
        $urls = [];
        $names = [];

        $candidate_links = $response->filter('a')->reduce(function (Crawler $node, $i) {
            return strpos($node->attr("href"), "https://opendorse.com/profile/") != false;
        });

        foreach ($candidate_links as $key => $link) {
            $link_crawler = new Crawler($link);

            if(strpos($link_crawler->attr("href"), "/url?q=https://opendorse.com/profile") === false) {
                continue;
            }

            $url = explode("&", str_replace("/url?q=", "", $link_crawler->attr("href")))[0];
            $url = explode("%", $url)[0];
            $urls[] = $url;
            $t = explode("-", $link_crawler->children("h3")->text())[0];
            $t = explode(",", $t)[0];
            $t = explode(":", $t)[0];
            $names[] = trim($t);
        }

        $result = [
            "urls" => $urls,
            "names" => $names
        ];

        yield $this->item($result);
    }
}
