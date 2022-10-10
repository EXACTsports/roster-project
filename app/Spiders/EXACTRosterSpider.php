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

class EXACTRosterSpider extends BasicSpider
{
    private $patterns = [
        "name" => '/^name/i',
        "image_url" => '/(^image)|(^img)/i',
        "position" => '/(^pos)|(^position)/i',
        "year" => '/(^year)|(^yr)/i',
        "home_town" => '/(^hometown)/i'
    ];

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
        $athletes = [];
        $status = [];
        $tables = [];

        $tables = $response->filter('table')->reduce(function (Crawler $node, $i) {
            return count($node->filter('tbody')->children('tr')) > 10;
        });

        $tb_crawler = null;

        foreach ($tables as $table) {
            $tb_crawler = new Crawler($table);
        }

        if($tb_crawler == null) {
            $status['msg'] = 'Not found';
            yield $this->item($status);
            return;
        }

        $heads = $tb_crawler->children('thead > tr')->first()->children('th');
        $headers = [];
        $indexs = [
            "name" => -1,
            "image_url" => -1,
            "position" => -1,
            "year" => -1,
            "home_town" => -1
        ];

        foreach ($heads as $key => $head) {
            $headers[] = trim($head->nodeValue);
        }

        foreach ($headers as $key1 => $header) {
            foreach ($indexs as $key2 => $index) {
                if(preg_match($this->patterns[$key2], $header) == 1)
                {
                    $indexs[$key2] = $key1;
                }
            }
        }

        $raws = $tb_crawler->children('tbody > tr');

        foreach ($raws as $raw) {
            $tr_crawler = new Crawler($raw);

            $tds = $tr_crawler->children('td');
            $athlete = [];
            foreach ($indexs as $key => $value) {
                if($key == 'image_url') {
                    $src = $tds->eq($value)->children('img')->first()->attr('data-src');
                    $athlete[$key] = $src;
                    continue;
                }
                $athlete[$key] = $tds->eq($value)->text();
            }
            $athletes[] = $athlete;
        }

        yield $this->item($athletes);
    }
}
