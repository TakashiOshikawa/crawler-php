<?php
namespace Mycrawler;

use Mycrawler\GoutteWrapper;

class Crawler
{


    /**
     * @var array $urls 最終的にXMLにするためのURL一覧 リクエスト済みのURL一覧でもある
     */
    private $urls = [];
    
    /**
     * @var array $request_url リクエスト待ちのURL一覧
     */
    private $request_url = [];
    
    /**
     * @var string $domain 取得したいページのドメイン
     */
    private $domain = '';
    
    /**
     * @var \Apiclient_Goutte $client goutteのclient
     */
    private $client = null;
    
    /**
     * @var int $request_counter リクエスト回数
     */
    private $request_counter = 0;
    
    /**
     * @var int $max_request リクエスト最大回数
     */
    private $max_request = 500;
    
    
    /**
     * construct
     * 
     * @param string $domain
     */
    public function __construct(string $domain)
    {
        $this->domain = $domain;
        $this->setRequestStartUrl($domain);
        $this->client = new GoutteWrapper();
    }
    
    /**
     * リクエストしたURLを取得
     * 
     * @return array
     */
    public function getUrls()
    {
        return $this->urls;
    }
    
    /**
     * リクエスト待ちが無くなるかリクエスト最大回数無くなるまでメソッドループ
     * 
     * @return \Component_Tasks_Sitemapgenerate_Crawler
     */
    public function run()
    {
        if ($this->request_counter > $this->max_request) {
            return $this;
        }
        if (!empty($this->request_url)) {
            $this->eachRequestUrl();
        }
        
        return $this;
    }
    
    /**
     * リクエスト開始のurlをリクエスト待ちに追加
     * 
     * @param string $domain
     * @param bool $is_https
     * @return \Component_Tasks_Sitemapgenerate_Crawler
     */
    private function setRequestStartUrl(string $domain, bool $is_https = true)
    {
        $url = ($is_https) ? 'https://' : 'http://';
        $url .= $domain.'/';
        $this->request_url[] = $url;
        return $this;
    }
    
    /**
     * 実行時点で存在するリクエスト待ち分のリクエスト処理
     */
    private function eachRequestUrl()
    {
        // request_urlにリクエスト待ちのURLが存在しないか見る
        foreach ($this->request_url as $url) {
            // 既にリクエストしたことのあるURLであればリクエストしない
            if ($this->isDuplicateRequestedUrl($url)) {
                continue;
            }

            // リクエスト以降の処理
            $this->request_counter++;
            $this->request($url);
        }

        $this->run();
    }
    
    /**
     * リクエストからリンクの取得、リクエスト済み、リクエスト待ちの更新を行う
     * 
     * @param string $url
     * @return boolean
     */
    private function request(string $url)
    {
        try {
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
        } catch (\Exception $e) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 有効でまだリクエストしていないURLを取得
     * 
     * @param array $links
     * @return array
     */
    private function getEnabledAndYetNotRequestUrl(array $links = [])
    {
        $enabled_urls = $this->getEnabledUrl($links);
        return $this->getYetNotRequestUrl($enabled_urls);
    }
    
    /**
     * 有効(設定したドメイン)なURLを取得
     * 
     * @param array $links
     * @return array
     */
    private function getEnabledUrl(array $links = [])
    {
        $enable_urls = [];
        foreach ($links as $link) {
            if (strpos($link, '://'.$this->domain) !== false) {
                $enable_urls[] = $link;
            }
        }

        return $enable_urls;
    }
    
    /**
     * まだリクエストしたことのないURLかつリクエスト待ちに無いURLをリクエスト待ちに追加していく
     * 
     * @param array $links
     */
    private function getYetNotRequestUrl(array $links)
    {
        foreach ($links as $link) {
            if (!$this->isDuplicateRequestedUrl($link) && !$this->isContainUrlInRequestUrl($link)) {
                $this->request_url[] = $link;
            }
        }
    }
    
    /**
     * 指定URLをrequest_urlから削除する
     * 
     * @param string $url
     */
    private function deleteUrlOfRequestUrl(string $url)
    {
        $key = array_search($url, $this->request_url);
        if ($key !== false) {
            unset($this->request_url[$key]);
        }
    }
    
    /**
     * リクエスト待ちURLの中に受け取ったURLが含まれているか判定
     * 
     * @param string $url
     * @return bool
     */
    private function isContainUrlInRequestUrl(string $url)
    {
        $key = array_search($url, $this->request_url);
        return ($key !== false);
    }
    
    /**
     * 既にリクエストしたURLか判定
     * 
     * @param string $url
     * @return bool
     */
    private function isDuplicateRequestedUrl(string $url)
    {
        $key = array_search($url, $this->urls);
        return ($key !== false);
    }
}
