<?php
include_once "./net_funcs.php";
include_once "./DB.php";
include_once "./HTTP_STATES.php";

POST(function () {
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);
    if ($data !== null && $data["username"] !== null && $data["password"] !== null && $data["first_name"] !== null && $data["last_name"] !== null && $data["email"] !== null) {
        $username = $data["username"];
        $password = $data["password"];
        $first_name = $data["first_name"];
        $last_name = $data["last_name"];
        $email = $data["email"];

        $database = new DB();
        $connection = $database->getConnection();
        $connection->execute("INSERT INTO users (username, first_name, last_name, email, password) values (?, ?, ?, ?, ?)", [$username, $first_name, $last_name, $email, password_hash($password, PASSWORD_DEFAULT)]);
        $connection->closeConnection();

    } else {
        status_exit(HTTP_STATES::BAD_REQUEST);
    }
});