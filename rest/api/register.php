<?php
include_once "./net_funcs.php";
include_once "./DB.php";
include_once "./HTTP_STATES.php";

callFunctionWithMethod(
    #[
        Method(HTTPMethod::POST),
        Consumes(ContentType::APPLICATION_JSON)
    ]
    function ($input_data) {
        $data = $input_data["input"];
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
            statusExit(HTTP_STATES::BAD_REQUEST);
        }
    }
);