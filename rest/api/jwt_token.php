<?php
include_once "./connection.php";
include_once "./net_funcs.php";
include_once "./settings.php";
function generate_jwt_tokens($user_id): array {
    $settings = get_settings();
    $access_token_exp = $settings["access_token_exp"];
    $refresh_token_exp = $settings["refresh_token_exp"];
    $private_key_path = $settings["private_key_path"];
    $phrase = $settings["pk_phrase"];

    $roles = [];
    $sub = $user_id;
    $iss = $_SERVER["REMOTE_ADDR"];
    $token_header = [
        "alg" => "HS256",
        "typ" => "JWT"
    ];

    try {
        $connection = get_connection();
        $result = false;
        $hash = [];

        $statement = $connection->prepare("CALL make_session(?)");

        try {
            $statement->execute([$sub]);
            $result = $statement->get_result();
            $hash = $result->fetch_assoc();
        } catch (Exception $exception) {
            status_exit(403);
        }

        $aud_access = $hash["access_token"];
        $aud_refresh = $hash["refresh_token"];

        $result->close();
        $statement->close();

        try {
            $statement = $connection->prepare('SELECT roles.name AS role FROM user_with_role JOIN roles ON roles.id = user_with_role.role_id JOIN users ON users.id = user_with_role.user_id WHERE users.id = ?');
            $statement->execute([$sub]);
            $result = $statement->get_result();
            while ($hash = $result->fetch_assoc()) {
                array_push($roles, $hash["role"]);
            }
        } catch (Exception $exception) {
            status_exit(404);
        }

        $result->close();
        $statement->close();
        $connection->close();

        $access_token_payload = [
            "iss" => $iss,
            "sub" => $sub,
            "aud" => $aud_access,
            "exp" => $access_token_exp,
            "roles" => $roles
        ];

        $refresh_token_payload = [
            "iss" => $iss,
            "sub" => $sub,
            "aud" => $aud_refresh,
            "exp" => $refresh_token_exp,
            "roles" => $roles
        ];

        $private_key = openssl_pkey_get_private($private_key_path, $phrase);

        $access_token = "";
        $refresh_token = "";
        openssl_private_encrypt(base64_encode(json_encode($token_header)) . "." . base64_encode(json_encode($access_token_payload)), $access_token, $private_key);
        openssl_private_encrypt(base64_encode(json_encode($token_header)) . "." . base64_encode(json_encode($refresh_token_payload)), $refresh_token, $private_key);

        return [
            "access" => base64_encode($access_token),
            "refresh" => base64_encode($refresh_token)
        ];
    } catch (Exception $exception) {
        echo $exception->getMessage();
        status_exit(500);
    }
    return [];
}

function decrypt_jwt_token($token): array {
    $csr_path = get_settings()["csr_path"];
    $process_token = base64_decode($token);

    openssl_csr_export($csr_path, $csr);
    $public_key = openssl_csr_get_public_key($csr);
    openssl_public_decrypt($process_token, $decrypted_token, $public_key);
    $exploded = explode(".", $decrypted_token);

    return [
        "header" => json_decode(base64_decode($exploded[0]), true),
        "payload" => json_decode(base64_decode($exploded[1]), true)
    ];
}
