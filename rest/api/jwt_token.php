<?php
include_once "./net_funcs.php";
include_once "./settings.php";
include_once "./DB.php";
include_once "./HTTP_STATES.php";
function generate_jwt_tokens($user_id): array {
    $settings = get_settings()["jwt"];
    $access_token_exp = time() + $settings["access_exp"];
    $refresh_token_exp = time() + $settings["refresh_exp"];
    try {
        $access_token_sha_key = base64_encode(random_bytes(256));
        $refresh_token_sha_key = base64_encode(random_bytes(256));
    } catch (Exception $exception) {
        statusExit(HTTP_STATES::INTERNAL_SERVER_ERROR, $exception->getMessage());
    }


    $sub = $user_id;
    $iss = $_SERVER["REMOTE_ADDR"];
    $token_header = [
        "alg" => "sha256",
        "typ" => "JWT"
    ];

    $database = new DB();
    $connection = $database->getConnection();
    $data = $connection->executeWithResponse("CALL make_session(?, ?, ?, ?, ?)", [$sub, date("Y-m-d H:i:s", $access_token_exp), date("Y-m-d H:i:s", $refresh_token_exp), $access_token_sha_key, $refresh_token_sha_key])[0];

    $aud = $data["id"];

    $connection->closeConnection();

    $access_token_payload = [
        "iss" => $iss,
        "sub" => $sub,
        "aud" => $aud,
        "exp" => $access_token_exp,
        "roles" => explode(",", $data["@roles"])
    ];

    $refresh_token_payload = [
        "iss" => $iss,
        "sub" => $sub,
        "aud" => $aud,
        "exp" => $refresh_token_exp,
        "roles" => explode(",", $data["@roles"])
    ];


    $access_token = base64_encode(json_encode($token_header)) . "." . base64_encode(json_encode($access_token_payload));
    $refresh_token = base64_encode(json_encode($token_header)) . "." . base64_encode(json_encode($refresh_token_payload));
    $access_token = $access_token . "." . base64_encode(hash_hmac("sha256", $access_token, $access_token_sha_key));
    $refresh_token = $refresh_token . "." . base64_encode(hash_hmac("sha256", $refresh_token, $refresh_token_sha_key));

    return [
        "access" => $access_token,
        "refresh" => $refresh_token
    ];
}

/**
 * @param $token
 * @param bool $type true - acc | false - ref
 * @return array
 */
function decrypt_jwt_token($token, bool $type): array {
    try {
        $exploded = explode(".", $token, 3);
        $database = new DB();
        $connection = $database->getConnection();
        $header = $exploded[0];
        $payload = $exploded[1];
        $verify_signature = base64_decode($exploded[2]);
        $header_data = json_decode(base64_decode($header), true);
        $payload_data = json_decode(base64_decode($payload), true);
        $data = $connection->executeWithResponse("SELECT " . ($type ? "acc_sha_key" : "ref_sha_key") . " AS sha_key FROM session WHERE id = ?", [$payload_data["aud"]])[0];

        $sha_key = $data["sha_key"];

        $connection->closeConnection();

        if (hash_equals($verify_signature, hash_hmac($header_data["alg"], $header . "." . $payload, $sha_key))) {
            return [
                "header" => $header_data,
                "payload" => $payload_data
            ];
        } else {
            statusExit(HTTP_STATES::FORBIDDEN);
        }
    } catch (Error $exception) {
        statusExit(HTTP_STATES::FORBIDDEN, $exception->getMessage());
    }
}
