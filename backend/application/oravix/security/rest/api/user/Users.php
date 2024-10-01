<?php

namespace oravix\security\rest\api\user;

use oravix\db\Database;
use oravix\exceptions\HttpException;
use oravix\HTTP\ContentType;
use oravix\HTTP\Controller;
use oravix\HTTP\HttpMethod;
use oravix\HTTP\HttpResponse;
use oravix\HTTP\HttpStates;
use oravix\HTTP\input\PageInput;
use oravix\HTTP\input\PageInputParams;
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
        #[PageInputParams("username", array("username", "first_name", "last_name", "email", "active"))] PageInput $pageInput
    ) {
        $connection = (new Database())->getConnection();
        $statement = $connection->prepare(sprintf("SELECT first_name, last_name, username, email, id, active FROM users ORDER BY %s %s LIMIT :number_of_records OFFSET :start_index", $pageInput->sortBy, $pageInput->ascending ? "ASC" : "DESC"));
        $statement->execute([
            "start_index" => $pageInput->page * $pageInput->rowsPerPage,
            "number_of_records" => $pageInput->rowsPerPage
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