<?php

namespace oravix\security\rest\api\user;

use oravix\db\Database;
use oravix\exceptions\HttpException;
use oravix\HTTP\Consumes;
use oravix\HTTP\ContentType;
use oravix\HTTP\Controller;
use oravix\HTTP\HttpMethod;
use oravix\HTTP\HttpResponse;
use oravix\HTTP\HttpStates;
use oravix\HTTP\input\Json;
use oravix\HTTP\input\PathVariable;
use oravix\HTTP\Produces;
use oravix\HTTP\Request;
use oravix\security\Secure;
use PDO;
use PDOException;

#[
    Controller("/user")
]
class User {
    private Database $database;

    public function __construct() {
        $this->database = new Database();
    }

    #[
        Request(
            "",
            HttpMethod::GET
        ),
        Produces(ContentType::APPLICATION_JSON),
        Secure
    ]
    function getUser(
        #[
            PathVariable("id", true)
        ] $userId
    ) {
        $connection = $this->database->getConnection();
        $statement = $connection->prepare('SELECT users.first_name, users.last_name, users.username, users.email, GROUP_CONCAT(roles.name) AS roles FROM users JOIN user_with_role ON user_with_role.user_id = users.id JOIN roles ON roles.id = user_with_role.role_id WHERE users.id = :id GROUP BY users.id');
        $statement->setFetchMode(PDO::FETCH_NAMED);
        $statement->execute([
            "id" => $userId
        ]);
        $data = $statement->fetch();
        $data["roles"] = explode(",", $data["roles"]);
        return new HttpResponse($data, status: !isset($data["first_name"]) ? HttpStates::NOT_FOUND : HttpStates::OK);
    }

    #[
        Request(
            "",
            HttpMethod::PUT
        ),
        Consumes(ContentType::APPLICATION_JSON),
        Secure
    ]
    function editUser(
        #[Json] UserUpdateData             $userUpdateData,
        #[PathVariable("id", true)] string $userId
    ) {
        $connection = $this->database->getConnection();
        $statement = $connection->prepare("UPDATE users SET username = :username, first_name = :first_name, last_name = :last_name WHERE id = :user_id");
        try {
            $statement->execute([
                "username" => $userUpdateData->username,
                "first_name" => $userUpdateData->firstName,
                "last_name" => $userUpdateData->lastName,
                "user_id" => $userId
            ]);
        } catch (PDOException $exception) {
            if ($exception->getCode() == 23000) {
                throw new HttpException(HttpStates::CONFLICT);
            } else {
                throw $exception;
            }
        }
        if ($statement->rowCount() === 0) {
            throw new HttpException(HttpStates::NOT_FOUND);
        }
    }

    #[
        Request(
            "",
            HttpMethod::DELETE
        ),
        Secure
    ]
    function removeUser(
        #[PathVariable("id", true)] string $userId
    ) {
        $connection = $this->database->getConnection();
        $statement = $connection->prepare('DELETE FROM users WHERE users.id = :user_id');
        $statement->execute([
            "user_id" => $userId
        ]);

        if ($statement->rowCount() === 0) {
            throw new HttpException(HttpStates::NOT_FOUND);
        }
    }

    #[
        Request(
            "/roles",
            HttpMethod::GET
        ),
        Produces(ContentType::APPLICATION_JSON),
        Secure
    ]
    function getUserRoles(
        #[
            PathVariable("id", true)
        ] $userId
    ) {
        $connection = $this->database->getConnection();
        $statement = $connection->prepare('SELECT id, name, description, level FROM user_with_role JOIN roles ON roles.id = role_id WHERE user_id = :id');
        $statement->setFetchMode(PDO::FETCH_NAMED);
        $statement->execute([
            "id" => $userId
        ]);
        $data = $statement->fetchAll();
        return new HttpResponse($data);
    }

    #[
        Request(
            "/roles",
            HttpMethod::DELETE
        ),
        Secure("admin")
    ]
    function removeRoleToUser(
        #[
            PathVariable("user_id", true)
        ] $userId,
        #[
            PathVariable("role_id", true)
        ] $roleId
    ) {
        $connection = $this->database->getConnection();
        $statement = $connection->prepare('DELETE FROM user_with_role WHERE user_id = :user_id AND role_id = :role_id');
        $statement->execute([
            "user_id" => $userId,
            "role_id" => $roleId
        ]);

        if ($statement->rowCount() === 0) {
            throw new HttpException(HttpStates::NOT_FOUND);
        }
    }

    #[
        Request(
            "/roles",
            HttpMethod::POST
        ),
        Produces(ContentType::APPLICATION_JSON),
        Secure("admin")
    ]
    function addRoleToUser(
        #[
            PathVariable("user_id", true)
        ] $userId,
        #[
            PathVariable("role_id", true)
        ] $roleId
    ) {
        $connection = $this->database->getConnection();
        $statement = $connection->prepare('INSERT INTO user_with_role(user_id, role_id) VALUES (:user_id, :role_id)');
        $statement->execute([
            "user_id" => $userId,
            "role_id" => $roleId
        ]);
    }

    #[
        Request("/roles/not_users", HttpMethod::GET),
        Produces(ContentType::APPLICATION_JSON),
        Secure("admin")
    ]
    function getAllRoles(#[PathVariable("page", true)] int        $page,
                         #[PathVariable("count", true)] int       $rowsPerPage,
                         #[PathVariable("user_id", true)]         $userId,
                         #[PathVariable("sort-by", false)] string $sortBy = "name",
                         #[PathVariable("asc", false)] bool       $ascending = true
    ) {
        $connection = $this->database->getConnection();
        if (!in_array($sortBy, array("name", "level"))) {
            throw new HttpException(HttpStates::BAD_REQUEST, "Please use existing column");
        }
        $statement = $connection->prepare(sprintf("SELECT id, name, description, level FROM roles WHERE id NOT IN (SELECT role_id FROM user_with_role WHERE user_id = :user_id) ORDER BY %s %s LIMIT :number_of_records OFFSET :start_index", $sortBy, $ascending ? "ASC" : "DESC"));
        $statement->execute([
            "start_index" => $page * $rowsPerPage,
            "number_of_records" => $rowsPerPage,
            "user_id" => $userId
        ]);
        $data["roles"] = $statement->fetchAll(PDO::FETCH_NAMED);
        $statement = $connection->prepare("SELECT COUNT(id) AS count FROM roles WHERE id NOT IN (SELECT role_id FROM user_with_role WHERE user_id = :user_id)");
        $statement->execute(["user_id" => $userId]);
        $data = [...$data, ...$statement->fetch(PDO::FETCH_NAMED)];
        return new HttpResponse($data, HttpStates::OK);
    }

}