<?php

namespace proxy\cache;

class NoCache implements CacheInterface {

    public function drop(string $tag) {
        return;
    }

    public function exists(string $tag) {
        return false;
    }

    public function get(string $tag) {
        return;
    }

    public function hasExpired(string $tag, string $expires) {
        return true;
    }

    public function onInit(array $params = array()) {
        
    }

    public function put(string $tag, $content) {
        return;
    }

}
