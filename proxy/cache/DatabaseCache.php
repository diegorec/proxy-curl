<?php

namespace proxy\cache;

use Medoo\Medoo;
use proxy\exception\NoDatabaseException;
use proxy\exception\EstructureDatabaseException;

class DatabaseCache implements CacheInterface {

    private $db;
    public $table = "cache";
    public $keyColumn;
    public $valueColumn;
    public $timeColumn;
    public $tagsColumn;

    public function onInit(array $params = array()) {
        if (!isset($params['database'])) {
            throw new NoDatabaseException("No se ha indicado la base de datos");
        }
        if (!isset($params['key-column'], $params['value-column'], $params['time-column'], $params['tags-column'])) {
            throw new EstructureDatabaseException("Debe indicar los campos key-column, value-column, time-column y tags-column con los nombres de las columnas de la base de datos");
        }
        $this->db = new Medoo($params['database']);
        $this->keyColumn = $params['key-column'];
        $this->valueColumn = $params['value-column'];
        $this->timeColumn = $params['time-column'];
        $this->tagsColumn = $params['tags-column'];
    }

    public function drop(string $name) {
        return $this->db->delete($this->table, [
                    $this->keyColumn => $name
        ]);
    }

    public function exists(string $name): bool {
        return $this->db->has($this->table, [
                    $this->keyColumn => $name
        ]);
    }

    public function get(string $name) {
        $value = $this->db->get($this->table,
                ["$this->valueColumn[JSON]"],
                [$this->keyColumn => $name]
        );
        return $value[$this->valueColumn];
    }

    public function hasExpired(string $name, string $expires): bool {
        $value = $this->db->get($this->table,
                [$this->timeColumn],
                [$this->keyColumn => $name]
        );
        $contentdate = $value[$this->timeColumn];
        $expira = strtotime("$contentdate $expires");
        $now = strtotime('now');
        return $now > $expira;
    }

    public function put(string $name, $content) {
        $this->db->insert($this->table, [
            $this->keyColumn => $name,
            "$this->valueColumn[JSON]" => $content
        ]);
    }

    public function pull(string $name) {
        $content = $this->get($name);
        $this->drop($name);
        return $content;
    }

    public function list(): Array {
        $directory = $this->db->select($this->table, [
            $this->keyColumn
        ]);
        return array_column($directory, $this->keyColumn);
    }

    public function flush() {
        $this->db->query("TRUNCATE TABLE $this->table");
    }

    public function count(): int {
        return $this->db->count($this->table);
    }

    public function purge(string $expires) {
        $directory = $this->list();
        array_walk($directory, function ($value) use ($expires) {
            $key = $value[$this->keyColumn];
            if ($this->hasExpired($key, $expires)) {
                $this->drop($key);
            }
        });
    }

    /**
     * Sobre un recurso cacheado, se pueden añadir tags
     * @param string $name
     * @param array $tags
     */
    public function addTags(string $name, Array $tags) {
        $this->db->update($this->table, [
            $this->tagsColumn => implode(",", $tags)
                ],
                [$this->keyColumn => $name]);
    }

    public function selectByTags(Array $tags) {
        return $this->db->select($this->table, [
                    $this->keyColumn
                        ],
                        [
                            "$this->tagsColumn[~]" => $tags
        ]);
    }

}
