<?php
include_once "./connection.php";
include_once "./net_funcs.php";

function verify_user($username, $password): string | false {
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
                return false;
            }
        } catch (ErrorException $exception) {
            return false;
        }

    } catch (Exception $e) {
        echo $e->getMessage();
        status_exit(500);
    }
}
