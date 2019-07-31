<?php

namespace proxy;

use \Curl\Curl;
use proxy\cache\CacheInterface;

class Proxy {

    public $curl;
    public $timeout = 10;
    public $connectTimeout = 100;
    public $userAgent = "gr:PROXY-CURL\\PROXY-CURL-CACHE";
    private $url;

    public function __construct(string $url) {
        $this->url = $url;
        $this->curl = new Curl();
        $this->curl->setTimeout($this->timeout);
        $this->curl->setConnectTimeout($this->connectTimeout);
        $this->curl->setUserAgent($this->userAgent);
    }

    public function get(string $ruta, CacheInterface $cache = null) {
        if (!is_null($cache) && $cache->exists() && !$cache->hasExpired()) {
            return $cache->get();
        }
        $this->curl->get("$this->url$ruta");
        if ($this->curl->error) {
            throw new ProxyExceptions($this->curl->errorMessage, $this->curl->errorCode);
        }
        if (!is_null($cache)) {
            $cache->put($this->curl->response);
        }
        return $this->curl->response;
    }
    
    public function setHeaders(Array $headers) {
        $this->curl->setHeaders($headers);        
    }

}
