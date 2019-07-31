<?php

namespace proxy\cache;

interface CacheInterface {

    public function __construct(string $tag, string $expires);

    public function get();

    public function put($content);

    public function drop();

    public function exists();

    public function hasExpired();
}
