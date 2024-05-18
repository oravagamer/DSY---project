<?php
include_once "./net_funcs.php";
include_once "./jwt_token.php";
include_once "./DB.php";
include_once "./HTTP_STATES.php";

POST(function () {
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);
    if ($data !== null && $data["refresh"] !== null && $data["access"] !== null) {
        $old_access_token_data = decrypt_jwt_token($data["access"], true);
        $old_refresh_token_data = decrypt_jwt_token($data["refresh"], false);

        // expiration verify
        if ($old_refresh_token_data["payload"]["exp"] > time() && $old_refresh_token_data["payload"]["aud"] === $old_access_token_data["payload"]["aud"]) {
            $database = new DB();
            $connection = $database->getConnection();
            $data = $connection->executeWithResponse("SELECT count(*) AS count FROM session WHERE id = ? AND status = TRUE", [$old_refresh_token_data["payload"]["aud"]])[0];

            $connection->closeStatement();
            if ($data["count"] !== 1) {
                status_exit(HTTP_STATES::FORBIDDEN);
            }

            $connection->execute("UPDATE session SET status = FALSE WHERE id = ?", [$old_refresh_token_data["payload"]["aud"]]);
            $connection->closeConnection();
            return_as_json(generate_jwt_tokens($old_access_token_data["payload"]["sub"]));
        } else {
            status_exit(HTTP_STATES::FORBIDDEN);
        }

    } else {
        status_exit(HTTP_STATES::BAD_REQUEST);
    }
});
