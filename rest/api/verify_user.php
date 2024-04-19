<?php
include_once "./connection.php";

function verify_user($username, $password) {
  try {
        $connection = get_connection();

        $statement = $connection->prepare('SELECT password FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $username]);
        $result = $statementt->get_result();

        $hash = $result->fetch_assoc();
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        }, E_WARNING);

        try {
            if (password_verify($password, $hash["password"])) {
                return true;
            }
            else {
                return false;
            }
        } catch (ErrorException $exception) {
            return false;
        }
        restore_error_handler();
        $connection->close();

    } catch (Exception $e) {
        http_response_code(500);
        return false;
    }
  return false;
}
