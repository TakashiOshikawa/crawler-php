<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use Mycrawler\Crawler;
use Mycrawler\GoutteWrapper;


$crawler = new Crawler();
$crawler
    ->setRequestStartUrl('https://joins-job.com/')
    ->setDomain('://joins-job.com')
    ->run();

var_dump($crawler->getUrls());
