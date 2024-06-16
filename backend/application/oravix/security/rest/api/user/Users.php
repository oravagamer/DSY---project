<?php

namespace oravix\security\rest\api\user;

use oravix\db\Database;
use oravix\HTTP\ContentType;
use oravix\HTTP\Controller;
use oravix\HTTP\HttpMethod;
use oravix\HTTP\HttpResponse;
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
    function getUsers() {
        $connection = (new Database())->getConnection();
        $statement = $connection->prepare('SELECT first_name, last_name, username, email, id FROM users');
        $statement->execute();
        $statement->setFetchMode(PDO::FETCH_NAMED);
        $data = $statement->fetchAll();
        return new HttpResponse(!$data ? [] : $data);
    }
}