<?php
include_once "./connection.php";
include_once "./net_funcs.php";
include_once "./jwt_token.php";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);
    if ($data !== null && $data["token"] !== null) {
        $old_ref_token = $data["token"];
        $old_token_data = decrypt_jwt_token($old_ref_token);

        // expiration verify
        if (true) {
            $connection = get_connection();

            // if not present fix bug
            $statement = $connection->prepare("UPDATE session SET status = FALSE WHERE refresh_token = ?;");
            try {
                $statement->execute([$old_token_data["payload"]["aud"]]);
                $result = $statement->get_result();
                $hash = $result->fetch_assoc();
                $result->close();

                echo print_r($hash, true);
            } catch (Exception $exception) {
                status_exit(403);
            }

            $statement->close();
            $connection->close();

            $tokens = generate_jwt_tokens($old_token_data["payload"]["sub"]);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($tokens);
        } else {
            status_exit(403);
        }

    } else {
        status_exit(400);
    }
}
