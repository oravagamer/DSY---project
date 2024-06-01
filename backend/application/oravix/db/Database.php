<?php

namespace oravix\db;

use oravix\HTTP\HttpStates;
use PDO;
use PDOException;

class Database {
    private ?PDO $connection = null;

    public function __construct(
        private string $host,
        private string $name,
        private string $user,
        private string $password,
        private string $port
    ) {
    }

    public function getConnection(): ?PDO {
        try {
            if (is_null($this->connection)) {
                $this->connection = new PDO("mysql:host=$this->host;dbname=$this->name;port->$this->port", $this->user, $this->password);
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                $this->connection->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
            }

            return $this->connection;
        } catch (PDOException $e) {
            statusExit(HttpStates::INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }

}