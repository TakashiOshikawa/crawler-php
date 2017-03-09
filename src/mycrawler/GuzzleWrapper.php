<?php

namespace Mycrawler;

use GuzzleHttp\Client;

class GuzzleWrapper
{
    private $guzzle = null;

    public function __construct(Client $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    // httpリクエストを送ってステータスコードを確認したい
    // レスポンスbodyを確認したい
}
