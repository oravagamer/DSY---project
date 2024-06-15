<?php
include_once "./jwt_token.php";
include_once "./net_funcs.php";
include_once "./verify_user.php";
include_once "./DB.php";
include_once "./HTTP_STATES.php";

cancelWarns();
function secure(): array {
    list($auth_token) = sscanf(apache_request_headers()["Authorization"], "Bearer %s");
    if (!$auth_token) {
        list($auth_token) = sscanf(apache_request_headers()["authorization"], "Bearer %s");
    }
    $access_token_data = decrypt_jwt_token($auth_token, true);

    if ($access_token_data["payload"]["exp"] < time()) {
        statusExit(HTTP_STATES::FORBIDDEN);
    } else {

        $database = new DB();
        $connection = $database->getConnection();
        $data = $connection->executeWithResponse("SELECT count(*) AS count FROM session WHERE id = ? AND status = TRUE AND user_id = ?", [$access_token_data["payload"]["aud"], $access_token_data["payload"]["sub"]])[0];
        $connection->closeConnection();

        if ($data["count"] !== 1) {
            statusExit(HTTP_STATES::FORBIDDEN);
        }


        return [
            "id" => $access_token_data["payload"]["sub"],
            "roles" => verify_user_with_id($access_token_data["payload"]["sub"]),
            "access_token" => $access_token_data["payload"]["aud"]
        ];

    }
}