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

class OpendorseDetailSpider extends BasicSpider
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
        $social = [
            "instagram" => "",
            "twitter" => "",
            "facebook" => "",
            "linkedin" => ""
        ];

        $candidate_links = $response->filter('a')->reduce(function (Crawler $node, $i) {
            $flag = (strpos($node->attr("href"), "instagram.com") != false && strpos($node->attr("href"), "instagram.com/opendorse") != true) ||
                    (strpos($node->attr("href"), "twitter.com") != false && strpos($node->attr("href"), "twitter.com/opendorse") != true) ||
                    (strpos($node->attr("href"), "linkedin.com") != false && strpos($node->attr("href"), "linkedin.com/company/opendorse") != true) ||
                    (strpos($node->attr("href"), "facebook.com") != false && strpos($node->attr("href"), "facebook.com/opendorse") != true);
            return $flag;
        });

        foreach ($candidate_links as $key => $link) {
            $social_crawler = new Crawler($link);

            $parse = parse_url($social_crawler->attr("href"));

            switch($parse["host"])
            {
                case "www.instagram.com":
                    $social["instagram"] = $social_crawler->attr("href");
                    break;
                case "www.twitter.com":
                    $social["twitter"] = $social_crawler->attr("href");
                    break;
                case "www.facebook.com":
                    $social["facebook"] = $social_crawler->attr("href");
                    break;
                case "www.linkedin.com":
                    $social["linkedin"] = $social_crawler->attr("href");
                    break;
            }
        }

        $result["social"] = $social;

        yield $this->item($result);
    }
}
