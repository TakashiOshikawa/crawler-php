<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use Mycrawler\Crawler;
use Mycrawler\GoutteWrapper;


$crawler = new Crawler('joins-job.com');
$urls = $crawler
    ->run()
    ->getUrls();

var_dump($urls);
