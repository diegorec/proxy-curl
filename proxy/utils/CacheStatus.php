<?php

namespace proxy\utils;

use proxy\cache\CacheInterface;

class CacheStatus {

    const OK = 0;
    const NOT_FOUND = 1;
    const EXPIRED = 2;

    /**
     * Valida que exista la caché y no esté caducada
     *
     * @param CacheInterface $cache Objeto caché con el que manejar los datos
     * @param string $name Nombre del recurso que queremos analizar
     * @param string $expires Fecha máxima en la que tendrá validez
     * @return int
     */
    public static function check(CacheInterface $cache, string $name, string $expires): int {
        $status = self::NOT_FOUND;
        if ($cache->exists($name)) {
            $status = self::OK;
            if ($cache->hasExpired($name, $expires)) {
                $status = self::EXPIRED;
            }
        }
        return $status;
    }

}
