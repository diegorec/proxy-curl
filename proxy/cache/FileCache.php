<?php

namespace proxy\cache;

class FileCache implements CacheInterface {

    public function onInit(Array $params = []) {
        
    }

    public function get(string $tag) {
        $json = file_get_contents($tag);
        return json_decode($json);
    }

    public function put(string $tag, $content) {
        return file_put_contents($tag, json_encode($content));
    }

    public function drop(string $tag) {
        return unlink($tag);
    }

    public function exists(string $tag) {
        return is_file($tag) && is_readable($tag);
    }

    public function hasExpired(string $tag, string $expires) {
        $filetime = filemtime($tag);
        $filedate = date("Y-m-d H:i:s", $filetime);
        $expira = strtotime("$filedate $expires");
        $now = strtotime('now');
        return $now > $expira;
    }

}
