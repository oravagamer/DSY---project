<?php

namespace oravix\security\rest\api\role;

use oravix\db\Database;
use oravix\exceptions\HttpException;
use oravix\HTTP\ContentType;
use oravix\HTTP\Controller;
use oravix\HTTP\HttpMethod;
use oravix\HTTP\HttpResponse;
use oravix\HTTP\HttpStates;
use oravix\HTTP\input\PathVariable;
use oravix\HTTP\Produces;
use oravix\HTTP\Request;
use oravix\security\Secure;
use oravix\security\SecurityUserId;
use PDO;

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
    function getRoles(
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
    function getAllRoles(#[PathVariable("page", true)] int        $page,
                         #[PathVariable("count", true)] int       $rowsPerPage,
                         #[PathVariable("sort-by", false)] string $sortBy = "name",
                         #[PathVariable("asc", false)] bool       $ascending = true) {
        $connection = $this->database->getConnection();
        if (!in_array($sortBy, array("name", "level"))) {
            throw new HttpException(HttpStates::BAD_REQUEST, "Please use existing column");
        }
        $statement = $connection->prepare(sprintf("SELECT id, name, description, level FROM roles ORDER BY %s %s LIMIT :number_of_records OFFSET :start_index", $sortBy, $ascending ? "ASC" : "DESC"));
        $statement->execute([
            "start_index" => $page * $rowsPerPage,
            "number_of_records" => $rowsPerPage
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
    }

}