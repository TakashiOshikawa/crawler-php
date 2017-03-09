<?php
namespace Mycrawler;

use Mycrawler\GoutteWrapper;

class Crawler
{

    // 最終的にXMLにするためのURL一覧
    // リクエスト済みのURL一覧でもある
    private $urls = [];

    // リクエスト待ちのURL一覧
    private $request_url = [];

    // 取得したいリンクに含まれるドメイン
    private $domain = '';

    // goutteのclient
    private $client = null;

    private $counter = 0;

    public function __construct()
    {
        $this->client = new GoutteWrapper();
    }

    public function setDomain(string $domain)
    {
        $this->domain = $domain;
        return $this;
    }

    public function setRequestStartUrl(string $url)
    {
        $this->request_url[] = $url;
        return $this;
    }

    public function getUrls()
    {
        return $this->urls;
    }

    public function run()
    {
        $this->doLoop();
    }

    private function doLoop()
    {
        if ($this->counter > 100) {
            return $this;
        }
        $this->counter++;
        if (!empty($this->request_url)) {
            $this->eachRequestUrl();
        }
    }

    private function eachRequestUrl()
    {
        // request_urlにリクエスト待ちのURLが存在しないか見る
        foreach ($this->request_url as $url) {
            // 既にリクエストしたことのあるURLであればリクエストしない
            if ($this->isDuplicateRequestedUrl($url)) {
                continue;
            }

            // リクエスト以降の処理
            $this->request($url);
        }

        $this->doLoop();
    }

    // リクエストからリンクの取得、リクエスト済み、リクエスト待ちの更新を行う
    private function request(string $url)
    {
        // リクエスト
        $this->client->requestTo($url);

        // ステータス確認
        if ($this->client->getStatus() != 200) {
            return false;
        }

        // リンクの取得 指定ドメイン
        $links = $this->client->getLinks();
        $this->getEnabledAndYetNotRequestUrl($links);

        // リクエスト待ちのURLから今回リクエストしたURLを削除する
        $this->deleteUrlOfRequestUrl($url);

        // リクエスト済みのURLとして追加
        $this->urls[] = $url;
    }

    // 有効でまだリクエストしていないURLを取得
    private function getEnabledAndYetNotRequestUrl(array $links = [])
    {
        $enabled_urls = $this->getEnabledUrl($links);
        return $this->getYetNotRequestUrl($enabled_urls);
    }

    // 有効(設定したドメイン)なURLを取得
    private function getEnabledUrl(array $links = [])
    {
        $enable_urls = [];
        foreach ($links as $link) {
            if (strpos($link, $this->domain) !== false) {
                $enable_urls[] = $link;
            }
        }

        return $enable_urls;
    }

    // まだリクエストしたことのないURLかつリクエスト待ちに無いURLをリクエスト待ちに追加していく
    private function getYetNotRequestUrl(array $links)
    {
        $yet_not_request_urls = [];
        foreach ($links as $link) {
            if (!$this->isDuplicateRequestedUrl($link) && !$this->isContainUrlInRequestUrl($link)) {
                $this->request_url[] = $link;
            }
        }
    }

    // 指定URLをrequest_urlから削除する
    private function deleteUrlOfRequestUrl(string $url)
    {
        $key = array_search($url, $this->request_url);
        if ($key !== false) {
            unset($this->request_url[$key]);
        }
    }

    // リクエスト待ちURLの中に受け取ったURLが含まれているか判定
    private function isContainUrlInRequestUrl(string $url)
    {
        $key = array_search($url, $this->request_url);
        return ($key !== false);
    }

    // 既にリクエストしたURLか判定
    private function isDuplicateRequestedUrl(string $url)
    {
        $key = array_search($url, $this->urls);
        return ($key !== false);
    }
}
