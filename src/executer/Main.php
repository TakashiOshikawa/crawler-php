<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use Mycrawler\Crawler;
use Mycrawler\GuzzleWrapper;
use Mycrawler\GoutteWrapper;


$crawler = new Crawler();
$crawler
    ->setRequestStartUrl('https://joins-job.com/')
    ->setDomain('://joins-job.com')
    ->run();

var_dump($crawler->getUrls());
/*$client = new Client([
    // Base URI is used with relative requests
    'base_uri' => 'http://httpbin.org',
    // You can set any number of default request options.
    'timeout'  => 2.0,
]);


$crawler = new Crawler();
$crawler->echoArgString('test');
echo "\n";
 */
