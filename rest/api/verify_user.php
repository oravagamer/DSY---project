<?php
include_once "./connection.php";
include_once "./net_funcs.php";

function verify_user($username, $password): string {
    try {
        $connection = get_connection();

        $statement = $connection->prepare('SELECT password, id FROM users WHERE username = ? OR email = ?');
        $statement->execute([$username, $username]);
        $result = $statement->get_result();

        $hash = $result->fetch_assoc();
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        }, E_WARNING);

        try {
            $result->close();
            $statement->close();
            $connection->close();
            if (password_verify($password, $hash["password"])) {
                return $hash["id"];
            } else {
                status_exit(403);
            }
        } catch (ErrorException $exception) {
            status_exit(500);
        }

    } catch (Exception $e) {
        status_exit(500);
    }
}

function verify_user_with_id($user_id): array {
    $roles = [];

    try {
        $connection = get_connection();

        $statement = $connection->prepare('SELECT roles.name AS role FROM user_with_role JOIN roles ON user_with_role.role_id = roles.id WHERE user_with_role.user_id = ?');
        $statement->execute([$user_id]);
        $result = $statement->get_result();

        while ($hash = $result->fetch_assoc()) {
            array_push($roles, $hash["role"]);
        }

        try {
            $result->close();
            $statement->close();
            $connection->close();

            if (sizeof($roles) < 1) {
                status_exit(403);
            } else {
                return $roles;
            }

        } catch (ErrorException $exception) {
            status_exit(500);
        }

    } catch (Exception $e) {
        echo $e->getMessage();
        status_exit(500);
    }
}
