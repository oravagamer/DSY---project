<?php
include_once "./connection.php";
include_once "./net_funcs.php";
POST(function () {
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);
    if ($data !== null && $data["username"] !== null && $data["password"] !== null && $data["first_name"] !== null && $data["last_name"] !== null && $data["email"] !== null) {
        $username = $data["username"];
        $password = $data["password"];
        $first_name = $data["first_name"];
        $last_name = $data["last_name"];
        $email = $data["email"];

        $connection = get_connection();

        $statement = $connection->prepare("INSERT INTO users (username, first_name, last_name, email, password) values (?, ?, ?, ?, ?)");
        try {
            $statement->execute([$username, $first_name, $last_name, $email, password_hash($password, PASSWORD_DEFAULT)]);
        } catch (Exception $exception) {
            status_exit(403);
        }

        $statement->close();
        $connection->close();

    } else {
        status_exit(400);
    }
});