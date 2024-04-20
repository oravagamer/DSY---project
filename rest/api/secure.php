<?php
include_once "./jwt_token.php";
include_once "./net_funcs.php";
include_once "./verify_user.php";

function secure(): array {
    list($auth_token) = sscanf(apache_request_headers()["Authorization"], "Bearer %s");
    $access_token_data = decrypt_jwt_token($auth_token, true);

    if ($access_token_data["payload"]["exp"] < time()) {
        status_exit(403);
    } else {

        $connection = get_connection();


        $statement = $connection->prepare("SELECT count(*) AS count FROM session WHERE id = ? AND status = TRUE AND user_id = ?");

        try {
            $statement->execute([$access_token_data["payload"]["aud"], $access_token_data["payload"]["sub"]]);
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

        return [
            "id" => $access_token_data["payload"]["sub"],
            "roles" => verify_user_with_id($access_token_data["payload"]["sub"]),
            "access_token" => $access_token_data["payload"]["aud"]
        ];

    }
}