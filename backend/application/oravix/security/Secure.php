<?php

namespace oravix\security;

use Attribute;
use Error;
use Exception;
use oravix\HTTP\HttpStates;
use PDO;

#[Attribute(Attribute::TARGET_METHOD)]
final class Secure {

    public function __construct(string|int $role = "default") {
        $connection = null;
        try {
            $connection = new PDO("mysql:host=" . $_ENV["settings"]["JWT_DB_SERVER"] . ";dbname=" . $_ENV["settings"]["JWT_DB_DATABASE_NAME"] . ";port=" . $_ENV["settings"]["JWT_DB_PORT"], $_ENV["settings"]["JWT_DB_USERNAME"], $_ENV["settings"]["JWT_DB_PASSWORD"]);
        } catch (Error|Exception $e) {
            statusExit(HttpStates::INTERNAL_SERVER_ERROR, $e->getMessage());
        }

        if (is_int($role)) {
            $statement = $connection->prepare("SELECT ((SELECT MIN(roles.level) FROM user_with_role JOIN roles ON user_with_role.role_id = roles.id WHERE user_id = :user_id GROUP BY user_id) <= :role_level) as res");
            $statement->execute([
                "user_id" => $_ENV["data"]["user-id"],
                "role_level" => $role
            ]);
        } else {
            $statement = $connection->prepare("SELECT ((SELECT id FROM roles WHERE name = :role) IN (SELECT role_id FROM user_with_role WHERE user_id = :user_id)) | ((SELECT level FROM roles WHERE name = :role) > (SELECT MIN(roles.level) AS cl FROM user_with_role JOIN roles ON user_with_role.role_id = roles.id WHERE user_id = :user_id GROUP BY user_id)) AS res");
            $statement->execute([
                "user_id" => $_ENV["data"]["user-id"],
                "role" => $role
            ]);

        }
        if (!$statement->fetch()["res"]) {
            statusExit(HttpStates::NOT_FOUND, "role");
        }

    }
}
