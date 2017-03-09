<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use Mycrawler\Crawler;


$client = new Client([
    // Base URI is used with relative requests
    'base_uri' => 'http://httpbin.org',
    // You can set any number of default request options.
    'timeout'  => 2.0,
]);


$crawler = new Crawler();
$crawler->echoArgString('test');
echo "\n";

