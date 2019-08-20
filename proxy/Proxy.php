<?php

namespace proxy;

use \Curl\Curl;
use proxy\cache\CacheInterface;

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

    public function remember(string $url, string $tag, string $expires = "+ 1 second") {
        $content = null;
        if ($this->checkCache($tag, $expires)) {
            $content = $this->cache->get($tag);
        } else {
            $content = $this->call($url);
            $this->cache->put($tag, $content);
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

    public function checkCache(string $tag, string $expires): bool {
        $is = false;
        if ($this->cache->exists($tag)) {
            if (!$this->cache->hasExpired($tag, $expires)) {
                $is = true;
            } else {
                $this->cache->drop($tag);
            }
        }
        return $is;
    }

}
