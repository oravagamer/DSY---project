<?php
include_once "./DB.php";
include_once "./HTTP_STATES.php";
include_once "./net_funcs.php";

function verify_user(string $username, string $password): string {
    $database = new DB();
    $connection = $database->getConnection();

    $data = $connection->executeWithResponse('SELECT password, id FROM users WHERE username = ? OR email = ?', [$username, $username])[0];
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }, E_WARNING);
    $connection->closeConnection();

    try {
        if (password_verify($password, $data["password"])) {
            return $data["id"];
        } else {
            statusExit(HTTP_STATES::FORBIDDEN);
        }
    } catch (Exception $exception) {
        statusExit(HTTP_STATES::FORBIDDEN, $exception->getMessage());
    }

}

function verify_user_with_id(string $user_id): array {
    $roles = [];
    $database = new DB();
    $connection = $database->getConnection();

    $data = $connection->executeWithResponse('SELECT roles.name AS role FROM user_with_role JOIN roles ON user_with_role.role_id = roles.id WHERE user_with_role.user_id = ?',
        [$user_id]);

    foreach ($data as $result) {
        array_push($roles, $result["role"]);
    }
    $connection->closeConnection();
    if (sizeof($roles) < 1) {
        statusExit(HTTP_STATES::FORBIDDEN);
    } else {
        return $roles;
    }

}
