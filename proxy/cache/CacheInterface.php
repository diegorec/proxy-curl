<?php

namespace proxy\cache;

interface CacheInterface {

    /**
     * Método de inicialización de las variables propias de cada controlador.
     * Este método debe ser llamado antes de operar sobre cualquier controlado.
     * Lanzará una excepción cuando no se indique una variable necesaria
     *
     * @param array $params Variables que deben indicarse al inicializar un controlador.
     */
    public function onInit(Array $params = []);

    /**
     * Recuperar el contenido de un recurso
     * 
     * @param string $name Nombre del recurso que queremos acceder
     */
    public function get(string $name);

    /**
     * Almacenar un recurso
     *
     * @param string $name Nombre del recurso que queremos acceder
     * @param type $content Contenido del recurso
     */
    public function put(string $name, $content);

    /**
     * Borrar un recurso
     * @param string $name Nombre del recurso que queremos acceder
     */
    public function drop(string $name);

    /**
     * Verifica que un recurso exista
     * 
     * @param string $name Nombre del recurso que queremos acceder
     * @return bool
     */
    public function exists(string $name): bool;

    /**
     * Verifica según la fecha de creación y el periodo indicado {{$expires}} que el recurso no haya caducado
     *
     * @param string $name Nombre del recurso que queremos acceder
     * @param string $expires Período en que se condifera caducado
     * @param bool
     */
    public function hasExpired(string $name, string $expires): bool;

    /**
     * Recuperar un recurso cacheado y acto seguido lo borra
     *
     * @param string $name Nombre del recurso que queremos acceder
     * @return mixed Contenido cacheado antes de ser borrado
     */
    public function pull(string $name);

    /**
     * Recuperar un listado con todos los recursos almacenados en la cache
     * 
     * @return array Listado de los recursos, sin su contenido
     */
    public function list(): Array;

    /**
     * Borra todos los registros almacenados en la cache indicada
     */
    public function flush();

    /**
     * Devuelve la cantidad de elementos almacenados en cache
     * @return int
     */
    public function count(): int;

    /**
     * Eliminar de la cache aquellos elementos que han caducado
     * @param string $expires Período en que se condifera caducado
     */
    public function purge(string $expires);
}
