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
        return new HttpResponse($data, status: !$data ? HttpStates::NOT_FOUND : HttpStates::OK);
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
        $statement = $connection->prepare("UPDATE users SET username = :username, first_name = :first_name, last_name = :last_name, email = :email WHERE id = :user_id");
        try {
            $statement->execute([
                "username" => $userUpdateData->username,
                "first_name" => $userUpdateData->firstName,
                "last_name" => $userUpdateData->lastName,
                "email" => $userUpdateData->email,
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

}