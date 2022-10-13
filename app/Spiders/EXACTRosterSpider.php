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
    private $patterns_table = [
        "name" => '/.*?name$/i',
        "image_url" => '/(^image)|(^img)/i',
        "position" => '/(^pos)|(^position)/i',
        "year" => '/(.*?year.?)$|(.*?yr.?)$|(.*?cl.?)$|(.*?class.?)$/i',
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
        $result = []; // result - status, athletes
        $athletes = []; // athletes array
        $tables = []; // crawled table

        // if the data is in table
        $tables = $response->filter('table')->reduce(function (Crawler $node, $i) {
            return count($node->children('tbody > tr')) > 10;
        });

        $tb_crawler = null;

        foreach ($tables as $table) {
            $tb_crawler = new Crawler($table);
        }

        if($tb_crawler == null) {
            $result['status'] = 'Not found';
            yield $this->item($result);
            return;
        }

        $heads = $tb_crawler->children('thead > tr')->first()->children('th');
        $head_cnt = count($heads);
        $headers = [];
        $indexes = [
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
            foreach ($indexes as $key2 => $index) {
                if(preg_match($this->patterns_table[$key2], $header) == 1)
                {
                    $indexes[$key2] = $key1;
                }
            }
        }

        $raws = $tb_crawler->children('tbody > tr');
        $raw_children_cnt = count($raws->first()->children());
        
        // if children's count is greater than heads, there might be more duplicated fields in each raw
        // we need to fix indexes
        if($raw_children_cnt > $head_cnt) {
            $first_raw = $raws->eq(7)->children();
            $remove_cnt = $raw_children_cnt - $head_cnt;
            for($i = 0; $i < $raw_children_cnt - 1; $i++) {
                $a = $first_raw->eq($i)->text();
                $b = $first_raw->eq($i + 1)->text();

                if(strlen($a) < strlen($b)) {
                    $c = $a;
                    $a = $b;
                    $b = $c;
                }

                // check duplicated fields
                if(strpos($a, $b) == true) {
                    $remove_cnt--;
                    foreach ($indexes as $key => $value) {
                        if($value > $i) {
                            $indexes[$key] = $value + 1;
                        }
                    }
                }

                if($remove_cnt == 0) {
                    break;
                }
            }
        }

        foreach ($raws as $raw) {
            $tr_crawler = new Crawler($raw);

            $tds = $tr_crawler->children();
            
            $athlete = [];
            foreach ($indexes as $key => $value) {
                // not found
                if($value == -1) {
                    // not found this index
                    $athlete[$key] = 'undefined';
                    continue;
                }

                // get image url
                if($key == 'image_url') {
                    $src = '';
                    if(count($tds->eq($value)->children('img')) != 0)
                        $src = $tds->eq($value)->children('img')->first()->attr('data-src');
                    $athlete[$key] = $src;
                    continue;
                }

                // if each field inclues ':'
                $text = explode(':', $tds->eq($value)->text());
                $text = trim(count($text) == 2 ? $text[1] : $text[0]);
                $athlete[$key] = $text;

                // remove .
                $text = trim($text, '.');

                if($key == 'home_town') {
                    // if home_town field inclues '/' - e.g. Beaumont, Texas / West Brook HS
                    $athlete[$key] = trim(trim(explode('/', $text)[0]), '.');
                    continue;
                }

                if($key == 'year') {
                    // if year is numeric, we need to change the value
                    if(is_numeric($text)) {
                        switch($text) {
                            case 1: $text = 'Fr'; break;
                            case 2: $text = 'So'; break;
                            case 3: $text = 'Jr'; break;
                            case 4:
                            case 5:
                            case 6: $text = 'Sr'; break;
                        }
                    }
                }

                $athlete[$key] = $text;
            }
            $athletes[] = $athlete;
        }

        $result['status'] = 'Success';
        $result['athletes'] = $athletes;

        yield $this->item($result);
    }
}
