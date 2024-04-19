<?php
include_once "./connection.php";

function verify_user($username, $password) {
  try {
        $c = getConnection();

        $stmt = $c->prepare('SELECT password FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $username]);
        $result = $stmt->get_result();

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
        $c->close();

    } catch (Exception $e) {
        http_response_code(500);
    }
}
