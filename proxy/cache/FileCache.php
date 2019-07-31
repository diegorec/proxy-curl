<?php

namespace proxy\cache;

class FileCache implements CacheInterface {

    private $tag;
    private $expires;

    public function __construct(string $tag, string $time) {
        $this->tag = $tag;
        $this->expires = $time;
    }

    public function get() {
        $json = file_get_contents($this->tag);
        return json_decode($json);
    }

    public function put($content) {
        return file_put_contents($this->tag, json_encode($content));
    }

    public function drop() {
        return unlink($this->tag);
    }

    public function exists() {
        return is_file($this->tag) && is_readable($this->tag);
    }

    public function hasExpired() {
        $filetime = filemtime($this->tag);
        $filedate = date("Y-m-d H:i:s", $filetime);
        $expira = strtotime("$filedate $this->expires");
        $now = strtotime('now');
        return $now > $expira;
    }

}
