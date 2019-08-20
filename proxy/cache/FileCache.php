<?php

namespace proxy\cache;

use proxy\exception\FileException;

class FileCache implements CacheInterface {

    public $dirname;

    public function onInit(Array $params = []) {
        if (!isset($params['dirname'])) {
            throw new FileException("Debes indicar el directorio en que se almacenarán los archivos");
        }
        $this->dirname = $params['dirname'];
        if (!is_dir($this->dirname)) {
            throw new FileException("No has indicado un directorio válido");
        }
    }

    public function get(string $name) {
        $json = file_get_contents("$this->dirname$name");
        return json_decode($json);
    }

    public function put(string $name, $content) {
        return file_put_contents("$this->dirname$name", json_encode($content));
    }

    public function drop(string $name) {
        return unlink("$this->dirname$name");
    }

    public function exists(string $name): bool {
        return is_file("$this->dirname$name") && is_readable("$this->dirname$name");
    }

    public function hasExpired(string $name, string $expires): bool {
        $filetime = filemtime("$this->dirname$name");
        $filedate = date("Y-m-d H:i:s", $filetime);
        $expira = strtotime("$filedate $expires");
        $now = strtotime('now');
        return $now > $expira;
    }

    public function pull(string $name) {
        $content = $this->get("$this->dirname$name");
        $this->drop("$this->dirname$name");
        return $content;
    }

    public function list(): Array {
        $directory = scandir($this->dirname);
        return array_diff($directory, ['.', '..']);
    }

    public function flush() {
        $directory = $this->list();
        array_walk($directory, function ($value) {
            $this->drop($value);
        });
    }

    public function purge(string $expires) {
        $directory = $this->list();
        array_walk($directory, function ($value) use ($expires) {
            if ($this->hasExpired($value, $expires)) {
                $this->drop($value);
            }
        });
    }

    public function count(): int {
        $directory = $this->list();
        return count($directory);
    }

}
