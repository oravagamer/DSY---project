<?php
include_once "./jwt_token.php";
include_once "./net_funcs.php";
include_once "./secure.php";
include_once "./DB.php";
include_once "./HTTPMethod.php";

callFunctionWithMethod(
    #[
        Method(HTTPMethod::GET),
        Produces(ContentType::APPLICATION_JSON),
        Secure
    ]
    function () {
        $database = new DB();
        $connection = $database->getConnection();
        $return_data = $connection->executeWithResponse('SELECT first_name, last_name, username, email, id FROM users');
        $connection->closeConnection();

        return_as_json($return_data);
    }
);
