<?php

namespace proxy\cache;

interface CacheInterface {

    public function onInit(Array $params = []);

    public function get(string $tag);

    public function put(string $tag, $content);

    public function drop(string $tag);

    public function exists(string $tag);

    public function hasExpired(string $tag, string $expires);
}
