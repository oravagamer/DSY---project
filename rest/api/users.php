<?php
include_once "./jwt_token.php";
include_once "./net_funcs.php";
include_once "./secure.php";
include_once "./DB.php";

$user = secure();

GET(function () {
    $database = new DB();
    $connection = $database->getConnection();
    $return_data = $connection->executeWithResponse('SELECT first_name, last_name, username, email, id FROM users');
    $connection->closeConnection();

    return_as_json($return_data);
});
