<?php

namespace oravix\security\rest\api\user;

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
use PDO;

#[
    Controller("/users")
]
class Users {
    #[
        Request(
            "",
            HttpMethod::GET
        ),
        Produces(ContentType::APPLICATION_JSON),
        Secure
    ]
    function getUsers(
        #[PathVariable("page", true)] int        $page,
        #[PathVariable("count", true)] int       $rowsPerPage,
        #[PathVariable("sort-by", false)] string $sortBy = "username",
        #[PathVariable("asc", false)] bool       $ascending = true
    ) {
        $connection = (new Database())->getConnection();
        if (!in_array($sortBy, array("username", "first_name", "last_name", "email"))) {
            throw new HttpException(HttpStates::BAD_REQUEST, "Please use existing column");
        }
        $statement = $connection->prepare(sprintf("SELECT first_name, last_name, username, email, id, active FROM users ORDER BY %s %s LIMIT :number_of_records OFFSET :start_index", $sortBy, $ascending ? "ASC" : "DESC"));
        $statement->execute([
            "start_index" => $page * $rowsPerPage,
            "number_of_records" => $rowsPerPage
        ]);
        $statement->setFetchMode(PDO::FETCH_NAMED);
        $data = $statement->fetchAll();
        $statement = $connection->prepare("SELECT COUNT(*) AS count FROM users");
        $statement->execute();
        $statement->setFetchMode(PDO::FETCH_NAMED);
        $data = [
            "users" => $data,
            "count" => $statement->fetch()["count"]
        ];
        return new HttpResponse(!$data ? [] : $data);
    }
}