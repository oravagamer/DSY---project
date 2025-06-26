<?php

namespace oravix\security\rest\api\role;

use oravix\db\Database;
use oravix\exceptions\HttpException;
use oravix\HTTP\Consumes;
use oravix\HTTP\ContentType;
use oravix\HTTP\Controller;
use oravix\HTTP\HttpMethod;
use oravix\HTTP\HttpResponse;
use oravix\HTTP\HttpStates;
use oravix\HTTP\input\Json;
use oravix\HTTP\input\PageInput;
use oravix\HTTP\input\PageInputParams;
use oravix\HTTP\input\PathVariable;
use oravix\HTTP\Produces;
use oravix\HTTP\Request;
use oravix\security\Secure;
use oravix\security\SecurityUserId;
use PDO;
use PDOException;

#[
    Controller("/role")
]
class Role {
    private Database $database;

    public function __construct() {
        $this->database = new Database();
    }

    #[
        Request("", HttpMethod::GET),
        Produces(ContentType::APPLICATION_JSON),
        Secure
    ]
    function getRolesOfUser(
        #[SecurityUserId] string $userId
    ) {
        $connection = $this->database->getConnection();
        $statement = $connection->prepare("SELECT roles.name, roles.level FROM roles RIGHT JOIN user_with_role ON user_with_role.role_id = roles.id WHERE user_with_role.user_id = :user_id");
        $statement->execute([
            "user_id" => $userId
        ]);
        return new HttpResponse($statement->fetchAll(PDO::FETCH_NAMED), HttpStates::OK);
    }

    #[
        Request("/all", HttpMethod::GET),
        Produces(ContentType::APPLICATION_JSON),
        Secure("admin")
    ]
    function getAllRoles(
        #[PageInputParams("username", array("name", "level"))] PageInput $pageInput
    ) {
        $connection = $this->database->getConnection();
        $statement = $connection->prepare(sprintf("SELECT id, name, description, level FROM roles ORDER BY %s %s LIMIT :number_of_records OFFSET :start_index", $pageInput->sortBy, $pageInput->ascending ? "ASC" : "DESC"));
        $statement->execute([
            "start_index" => $pageInput->page * $pageInput->rowsPerPage,
            "number_of_records" => $pageInput->rowsPerPage
        ]);
        $data["roles"] = $statement->fetchAll(PDO::FETCH_NAMED);
        $statement = $connection->prepare("SELECT COUNT(*) AS count FROM roles");
        $statement->execute();
        $data = [...$data, ...$statement->fetch(PDO::FETCH_NAMED)];
        return new HttpResponse($data, HttpStates::OK);
    }

    #[
        Request("/single", HttpMethod::GET),
        Produces(ContentType::APPLICATION_JSON),
        Secure("admin")
    ]
    function getRole(
        #[PathVariable("role", true)] string $roleId
    ) {
        $connection = $this->database->getConnection();
        $statement = $connection->prepare("SELECT id, name, description, level FROM roles WHERE id = :role_id");
        $statement->execute([
            "role_id" => $roleId
        ]);
        $data = $statement->fetch(PDO::FETCH_NAMED);
        return new HttpResponse($data);
    }

    #[
        Request("/single", HttpMethod::PUT),
        Consumes(ContentType::APPLICATION_JSON),
        Secure("admin")
    ]
    function updateRole(
        #[PathVariable("role", true)] string $roleId,
        #[Json] RoleUpdateData               $roleUpdateData
    ) {
        $sql = "UPDATE roles SET";
        $connection = $this->database->getConnection();
        if (isset($roleUpdateData->name)) {
            $sql .= " name = :name";
        }
        if (isset($roleUpdateData->description)) {
            if (isset($roleUpdateData->name)) {
                $sql .= ",";
            }
            $sql .= " description = :description";
        }
        if (isset($roleUpdateData->level)) {
            if (isset($roleUpdateData->name) || isset($roleUpdateData->description)) {
                $sql .= ",";
            }
            $sql .= " level = :level";
        }
        $sql .= " WHERE id = :role_id AND name != 'admin' AND name != 'default'";
        try {
            $statement = $connection->prepare($sql);
        } catch (\Exception $e) {
            var_dump($e);
        }
        $statement->bindParam("role_id", $roleId);
        if (isset($roleUpdateData->name)) {
            $statement->bindParam("name", $roleUpdateData->name);
        }
        if (isset($roleUpdateData->description)) {
            $statement->bindParam("description", $roleUpdateData->description);
        }
        if (isset($roleUpdateData->level)) {
            $statement->bindParam("level", $roleUpdateData->level, PDO::PARAM_INT);
        }
        $statement->execute();
        if ($statement->rowCount() !== 1) {
            throw new HttpException(HttpStates::NOT_FOUND);
        }
    }

    #[
        Request("/single", HttpMethod::POST),
        Secure("admin"),
        Consumes(ContentType::APPLICATION_JSON)
    ]
    function addRole(
        #[Json] RoleData $roleData
    ) {
        $connection = $this->database->getConnection();
        $statement = $connection->prepare("INSERT INTO roles(name, description, level) VALUES (:name, :description, :level)");
        try {
            $statement->bindParam("name", $roleData->name);
            $statement->bindParam("description", $roleData->description);
            $statement->bindParam("level", $roleData->level, PDO::PARAM_INT);
            $statement->execute();
        } catch (PDOException $exception) {
            if ($exception->getCode() == 23000) {
                throw new HttpException(HttpStates::CONFLICT);
            } else {
                throw $exception;
            }
        }
    }

    #[
        Request("/single", HttpMethod::DELETE),
        Secure("admin")
    ]
    function removeRole(
        #[PathVariable("role", true)] string $roleId
    ) {
        $connection = $this->database->getConnection();
        $statement = $connection->prepare("DELETE FROM roles WHERE id = :role_id AND name <> 'admin' AND name <> 'default'");
        $statement->execute([
            "role_id" => $roleId
        ]);
        if ($statement->rowCount() !== 1) {
            throw new HttpException(HttpStates::NOT_FOUND);
        }
    }
}
