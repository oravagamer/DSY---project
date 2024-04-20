<?php
include_once "./connection.php";
include_once "./net_funcs.php";
include_once "./settings.php";
function generate_jwt_tokens($user_id): array {
    $settings = get_settings();
    $access_token_exp = time() + $settings["access_token_exp"];
    $refresh_token_exp = time() + $settings["refresh_token_exp"];
    try {
        $access_token_sha_key = base64_encode(random_bytes(256));
        $refresh_token_sha_key = base64_encode(random_bytes(256));
    } catch (Exception $e) {
        status_exit(500);
    }


    $sub = $user_id;
    $iss = $_SERVER["REMOTE_ADDR"];
    $token_header = [
        "alg" => "sha256",
        "typ" => "JWT"
    ];

    try {
        $connection = get_connection();
        $result = null;
        $hash = [];

        $statement = $connection->prepare("CALL make_session(?, ?, ?, ?, ?)");

        try {
            $statement->execute([$sub, date("Y-m-d H:i:s", $access_token_exp), date("Y-m-d H:i:s", $refresh_token_exp), $access_token_sha_key, $refresh_token_sha_key]);
            $result = $statement->get_result();
            $hash = $result->fetch_assoc();
        } catch (Exception $exception) {
            status_exit(403);
        }

        $aud = $hash["id"];

        $result->close();
        $statement->close();
        $connection->close();

        $access_token_payload = [
            "iss" => $iss,
            "sub" => $sub,
            "aud" => $aud,
            "exp" => $access_token_exp
        ];

        $refresh_token_payload = [
            "iss" => $iss,
            "sub" => $sub,
            "aud" => $aud,
            "exp" => $refresh_token_exp
        ];

        $access_token = base64_encode(json_encode($token_header)) . "." . base64_encode(json_encode($access_token_payload));
        $refresh_token = base64_encode(json_encode($token_header)) . "." . base64_encode(json_encode($refresh_token_payload));
        $access_token = $access_token . "." . base64_encode(hash_hmac("sha256", $access_token, $access_token_sha_key));
        $refresh_token = $refresh_token . "." . base64_encode(hash_hmac("sha256", $refresh_token, $refresh_token_sha_key));

        return [
            "access" => $access_token,
            "refresh" => $refresh_token
        ];
    } catch (Exception $exception) {
        echo $exception->getMessage();
        status_exit(500);
    }
    return [];
}

/**
 * @param $token
 * @param bool $type true - acc | false - ref
 * @return array
 */
function decrypt_jwt_token($token, bool $type): array {
    try {
        $exploded = explode(".", $token, 3);
        $connection = get_connection();
        $header = $exploded[0];
        $payload = $exploded[1];
        $verify_signature = base64_decode($exploded[2]);
        $header_data = json_decode(base64_decode($header), true);
        $payload_data = json_decode(base64_decode($payload), true);
        $sha_key = "";

        if ($type) {
            $statement = $connection->prepare("SELECT acc_sha_key AS sha_key FROM session WHERE id = ?");
        } else {
            $statement = $connection->prepare("SELECT ref_sha_key AS sha_key FROM session WHERE id = ?");
        }

        try {
            $statement->execute([$payload_data["aud"]]);
            $result = $statement->get_result();
            $hash = $result->fetch_assoc();
            $sha_key = $hash["sha_key"];

            $result->close();
            $statement->close();
            $connection->close();
        } catch (Exception $exception) {
            status_exit(403);
        }

        if (hash_equals($verify_signature, hash_hmac($header_data["alg"], $header . "." . $payload, $sha_key))) {
            return [
                "header" => $header_data,
                "payload" => $payload_data
            ];
        } else {
            status_exit(403);
        }
    } catch (Error $exception) {
        status_exit(403);
    }
}
