<?php
include_once "./Connection.php";
class DB {
    private string $hostname;
    private string $username;
    private string $password;
    private string $database;
    private string $port;

    function __construct() {
        $settings = get_settings()["db"];
        $this->hostname = $settings["server"];
        $this->username = $settings["username"];
        $this->password = $settings["password"];
        $this->database = $settings["db_name"];
        $this->port = $settings["port"];
    }

    public function getConnection(): Connection {
        return new Connection($this->hostname, $this->username, $this->password, $this->database, $this->port);
    }
}