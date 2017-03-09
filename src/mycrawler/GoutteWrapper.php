<?php

namespace Mycrawler;

use Goutte\Client;

class GoutteWrapper
{

    private $client = null;

    private $crawler = null;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function requestTo(string $url, string $http_method = 'GET')
    {
        $this->crawler = $this->client->request($http_method, $url);
        return $this;
    }

    public function getResponse()
    {
        return $this->client->getResponse();
    }

    public function getStatus()
    {
        return $this->client->getResponse()->getStatus();
    }

    public function getLinks()
    {
        return $this->crawler->filter('a')->each(function($node) {
            return $node->attr('href');
        });
    }
}
