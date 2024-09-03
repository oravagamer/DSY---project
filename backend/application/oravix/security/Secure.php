<?php

namespace oravix\security;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Secure {

    public function __construct($role = "default" string|int) {
        PDO|null $connection = null;
        try {
            $connection = new PDO("mysql:host=" . $_ENV["settings"]["JWT_DB_SERVER"] . ";dbname=" . $_ENV["settings"]["JWT_DB_DATABASE_NAME"] . ";port=" . $_ENV["settings"]["JWT_DB_PORT"], $_ENV["settings"]["JWT_DB_USERNAME"], $_ENV["settings"]["JWT_DB_PASSWORD"]);
        } catch (Error|Exception $e) {
            statusExit(HttpStates::INTERNAL_SERVER_ERROR, $e->getMessage());
        }
        
    }
}
