<?php

namespace proxy;

use \Curl\Curl;
use proxy\cache\CacheInterface;
use proxy\utils\CacheStatus;

class Proxy {

    public $curl;
    public $cache;
    public $timeout = 10;
    public $connectTimeout = 100;
    public $userAgent = "gr:PROXY-CURL\\PROXY-CURL-CACHE";

    public function __construct(CacheInterface $cache) {
        $this->curl = new Curl();
        $this->curl->setTimeout($this->timeout);
        $this->curl->setConnectTimeout($this->connectTimeout);
        $this->curl->setUserAgent($this->userAgent);
        $this->cache = $cache;
    }

    public function remember(string $url, string $name, string $expires = "+ 10 year") {
        $content = null;
        $cachestatus = CacheStatus::check($this->cache, $name, $expires);
        if ($cachestatus === CacheStatus::OK) {
            $content = $this->cache->get($name);
        } else {
            if ($cachestatus === CacheStatus::EXPIRED) {
                $this->cache->drop($name);
            }
            $content = $this->call($url);
            $this->cache->put($name, $content);
        }
        return $content;
    }

    public function call(string $url) {
        $this->curl->get($url);
        if ($this->curl->error) {
            throw new ProxyExceptions($this->curl->errorMessage, $this->curl->errorCode);
        }
        return $this->curl->response;
    }

}
