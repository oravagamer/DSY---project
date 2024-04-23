<?php
include_once "./jwt_token.php";
include_once "./net_funcs.php";
include_once "./secure.php";

$user = secure();

GET(function () {
    global $user;
    $return_data = [];
    try {
        $connection = get_connection();
        $statement = null;

        if (in_array("admin", $user["roles"])) {
            $statement = $connection->prepare('SELECT first_name, last_name, username, email, id FROM users');
            $statement->execute();
        } else {
            $statement = $connection->prepare('SELECT first_name, last_name, username, email, id FROM users WHERE id = ?');
            $statement->execute([$user["id"]]);
        }
        $result = $statement->get_result();

        while ($hash = $result->fetch_assoc()) {
            array_push($return_data, $hash);
        }

        try {
            $result->close();
            $statement->close();
            $connection->close();
        } catch (ErrorException $exception) {
            status_exit(500);
        }

    } catch (Exception $e) {
        echo $e->getMessage();
        status_exit(500);
    }

    return_as_json($return_data);
});
