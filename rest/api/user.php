<?php
include_once "./jwt_token.php";
include_once "./net_funcs.php";
include_once "./secure.php";
include_once "./DB.php";
include_once "./HTTP_STATES.php";

$user = secure();

GET(function () {
    $url = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $parts = parse_url($url);
    parse_str($parts['query'], $query);
    if (isset($query["id"])) {
        $database = new DB();
        $connection = $database->getConnection();
        $data = $connection->executeWithResponse('SELECT users.first_name, users.last_name, users.username, users.email, GROUP_CONCAT(roles.name) AS roles FROM users JOIN user_with_role ON user_with_role.user_id = users.id JOIN roles ON roles.id = user_with_role.role_id WHERE users.id = ? GROUP BY users.id', [$query["id"]])[0];
        $connection->closeConnection();
        $data["roles"] = explode(",", $data["roles"]);
        return_as_json($data);
    } else {
        status_exit(HTTP_STATES::NOT_FOUND);
    }
});
PUT(function () {
    $url = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $parts = parse_url($url);
    parse_str($parts['query'], $query);

    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    if (key_exists("id", $query) && isset($data)) {
        $username = $data["username"];
        $first_name = $data["first_name"];
        $last_name = $data["last_name"];
        $email = $data["email"];

        $database = new DB();
        $connection = $database->getConnection();
        $update_data = [];
        $sql_query = 'UPDATE users SET';

        if (isset($username)) {
            $sql_query = $sql_query . " username = ?,";
            array_push($update_data, $username);
        }
        if (isset($first_name)) {
            $sql_query = $sql_query . " first_name = ?,";
            array_push($update_data, $first_name);
        }
        if (isset($last_name)) {
            $sql_query = $sql_query . " last_name = ?,";
            array_push($update_data, $last_name);
        }
        if (isset($email)) {
            $sql_query = $sql_query . " email = ?,";
            array_push($update_data, $email);
        }
        array_push($update_data, $query["id"]);

        if ($sql_query[strlen($sql_query) - 1] === ",") {
            $str_split = str_split($sql_query);
            array_splice($str_split, strlen($sql_query) - 1, 1, "");
            $sql_query = implode("", $str_split);
            unset($str_split);
        }
        $sql_query = $sql_query . " WHERE id = ?";
        $connection->execute($sql_query, $update_data);
        if ($connection->getStatement()->affected_rows === 0) {
            status_exit(HTTP_STATES::NOT_FOUND);
        }
        $connection->closeConnection();
    }
});
DELETE(function () {
    $url = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $parts = parse_url($url);
    parse_str($parts['query'], $query);
    if (isset($query["id"])) {
        $database = new DB();
        $connection = $database->getConnection();
        $connection->execute('DELETE FROM users WHERE users.id = ?', [$query["id"]]);
        if ($connection->getStatement()->affected_rows !== 1) {
            status_exit(HTTP_STATES::NOT_FOUND);
        }
        $connection->closeConnection();

    } else {
        status_exit(HTTP_STATES::BAD_REQUEST);
    }
});
