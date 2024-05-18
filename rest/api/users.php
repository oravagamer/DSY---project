<?php
include_once "./jwt_token.php";
include_once "./net_funcs.php";
include_once "./secure.php";

$user = secure();

GET(function () {
    $return_data = [];
    try {
        $connection = get_connection();
        $statement = $connection->prepare('SELECT first_name, last_name, username, email, id FROM users');
        $statement->execute();
        $result = $statement->get_result();

        while ($hash = $result->fetch_assoc()) {
            array_push($return_data, $hash);
        }

        $connection->close();

    } catch (Exception $e) {
        echo $e->getMessage();
        status_exit(500);
    }

    return_as_json($return_data);
});
