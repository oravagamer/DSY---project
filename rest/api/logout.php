<?php
include_once "./connection.php";
include_once "./net_funcs.php";
include_once "./jwt_token.php";
POST(function () {
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);
    if ($data !== null && $data["refresh"] !== null && $data["access"] !== null) {
        $old_access_token_data = decrypt_jwt_token($data["access"], true);
        $old_refresh_token_data = decrypt_jwt_token($data["refresh"], false);

        if ($old_refresh_token_data["payload"]["exp"] > time() && $old_refresh_token_data["payload"]["aud"] === $old_access_token_data["payload"]["aud"]) {
            $connection = get_connection();


            $statement = $connection->prepare("SELECT count(*) AS count FROM session WHERE id = ? AND status = TRUE");

            try {
                $statement->execute([$old_refresh_token_data["payload"]["aud"]]);
                $result = $statement->get_result();
                $hash = $result->fetch_assoc();
                $result->close();
                $statement->close();

                if ($hash["count"] !== 1) {
                    status_exit(403);
                }

            } catch (Exception $exception) {
                status_exit(403);
            }

            $statement = $connection->prepare("UPDATE session SET status = FALSE WHERE id = ?");
            try {
                $statement->execute([$old_refresh_token_data["payload"]["aud"]]);
            } catch (Exception $exception) {
                status_exit(500);
            }

            $statement->close();
            $connection->close();

            redirect("/");
            status_exit(200);
        } else {
            status_exit(403);
        }

    } else {
        status_exit(400);
    }
});
