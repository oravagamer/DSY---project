<?php
include_once "./jwt_token.php";
include_once "./verify_user.php";
include_once "./net_funcs.php";
POST(function () {
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);
    if ($data !== null && $data["username"] !== null && $data["password"] !== null) {
        $username = $data["username"];
        $password = $data["password"];
        $user_id = verify_user($username, $password);
        return_as_json(generate_jwt_tokens($user_id));
    } else {
        status_exit(400);
    }
});

