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

    public function onInit(array $params = array()) {
        if (!isset($params['database'])) {
            throw new NoDatabaseException("No se ha indicado la base de datos");
        }
        if (!isset($params['key-column'], $params['value-column'], $params['time-column'])) {
            throw new EstructureDatabaseException("Debe indicar los campos key-column, value-column y time-column con los nombres de las columnas de la base de datos");
        }
        $this->db = new Medoo($params['database']);
        $this->keyColumn = $params['key-column'];
        $this->valueColumn = $params['value-column'];
        $this->timeColumn = $params['time-column'];
    }

    public function drop(string $tag) {
        return $this->db->delete($this->table, [
                    $this->keyColumn => $tag
        ]);
    }

    public function exists(string $tag) {
        return $this->db->has($this->table, [
                    $this->keyColumn => $tag
        ]);
    }

    public function get(string $tag) {
        $value = $this->db->get($this->table,
                ["$this->valueColumn[JSON]"],
                [$this->keyColumn => $tag]
        );
        return $value[$this->valueColumn];
    }

    public function hasExpired(string $tag, string $expires) {
        $value = $this->db->get($this->table,
                [$this->timeColumn],
                [$this->keyColumn => $tag]
        );
        $contentdate = $value[$this->timeColumn];
        $expira = strtotime("$contentdate $expires");
        $now = strtotime('now');
        return $now > $expira;
    }

    public function put(string $tag, $content) {
        $this->db->insert($this->table, [
            $this->keyColumn => $tag,
            "$this->valueColumn[JSON]" => $content
        ]);
    }

}
