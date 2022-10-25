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

use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNull;

class GoogleTwitterSpider extends BasicSpider
{
    public array $startUrls = [
        //
    ];

    private $twitter_search_res_h3_pattern = "/(\(@\w+\)|\(\w+@\)) (\/|-) twitter/i";

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

        $candidate_links = $response->filter('a')->reduce(function (Crawler $node, $i) {
            return strpos($node->attr("href"), "https://twitter.com") !== false || strpos($node->attr("href"), "https://mobile.twitter.com") !== false;
        });

        foreach ($candidate_links as $key => $link) {
            $link_crawler = new Crawler($link);

            // check only @ twitter link in h3(title) - regx = /\(@\w+\) \/ twitter/i
            if(count($link_crawler->children("h3")) && preg_match($this->twitter_search_res_h3_pattern, $link_crawler->children("h3")->text())) {
                $h3 = $link_crawler->children("h3")->text();
                $explode_arr = explode("(", $h3);
                $grep_arr = preg_grep("/(^@\w+\))|(\w+@\))/i", $explode_arr);
                
                // get twitter id
                $twitter_id = explode(")", $grep_arr[1] ?? "")[0];
                
                // get description
                $description = $link_crawler->closest("div")->closest("div")->siblings()->first();

                $result[] = array(
                    "name" => trim(explode("(", explode("(@", $h3)[0])[0]),
                    "twitter_id" => $twitter_id,
                    "description" => $description->text(),
                );
            }
        }

        yield $this->item($result);
    }
}
