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

    public function get(
            string $url,
            string $tag,
            string $expires = "+ 1 second"
    ) {
        if ($this->cache->exists($tag)) {
            if (!$this->cache->hasExpired($tag, $expires)) {
                return $this->cache->get($tag);
            } else {
                $this->cache->drop($tag);
            }
        }
        $this->curl->get($url);
        if ($this->curl->error) {
            throw new ProxyExceptions($this->curl->errorMessage, $this->curl->errorCode);
        }
        if (!is_null($this->cache)) {
            $this->cache->put($tag, $this->curl->response);
        }
        return $this->curl->response;
    }

    public function setHeaders(Array $headers) {
        $this->curl->setHeaders($headers);
    }

}
