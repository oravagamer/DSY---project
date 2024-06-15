<?php

namespace user;

use oravix\db\Database;
use oravix\HTTP\ContentType;
use oravix\HTTP\Controller;
use oravix\HTTP\HttpMethod;
use oravix\HTTP\HttpResponse;
use oravix\HTTP\HttpStates;
use oravix\HTTP\input\PathVariable;
use oravix\HTTP\Produces;
use oravix\HTTP\Request;
use oravix\security\Secure;
use PDO;

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
        ] $id
    ) {
        $connection = $this->database->getConnection();
        $statement = $connection->prepare('SELECT users.first_name, users.last_name, users.username, users.email, GROUP_CONCAT(roles.name) AS roles FROM users JOIN user_with_role ON user_with_role.user_id = users.id JOIN roles ON roles.id = user_with_role.role_id WHERE users.id = :id GROUP BY users.id');
        $statement->setFetchMode(PDO::FETCH_NAMED);
        $statement->execute([
            "id" => $id
        ]);
        $data = $statement->fetch();
        $data["roles"] = explode(",", $data["roles"]);
        return new HttpResponse($data, status: !$data ? HttpStates::NOT_FOUND : HttpStates::OK);
    }

    function editUser() {

    }

    function removeUser() {

    }

}