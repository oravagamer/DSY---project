<?php

namespace oravix\db;

use oravix\HTTP\HttpStates;
use PDO;
use PDOException;

class Database {
    private ?PDO $connection = null;
    private static string $hostname;
        private static string $dbName;
        private static string $username;
        private static string $password;
        private static string $port;

    public function __construct(
        ?string $hostname = null,
        ?string $dbName = null,
        ?string $username = null,
        ?string $password = null,
        ?string $port = null,
    ) {
        self::$hostname = is_null($hostname) ? $_ENV["settings"]["DB_SERVER"] : $hostname;
        self::$dbName = is_null($dbName) ? $_ENV["settings"]["DB_DATABASE_NAME"] : $dbName;
        self::$username = is_null($username) ? $_ENV["settings"]["DB_USERNAME"] : $username;
        self::$password = is_null($password) ? $_ENV["settings"]["DB_PASSWORD"] : $password;
        self::$port = is_null($port) ? $_ENV["settings"]["DB_PORT"] : $port;
    }

    public function getConnection(): ?PDO {
        try {
            if (is_null($this->connection)) {
                $this->connection = new PDO("mysql:host=" . self::$hostname . ";dbname=" . self::$dbName . ";port->" . self::$port, self::$username, self::$password);
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