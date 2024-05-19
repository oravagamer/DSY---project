<?php
include_once "./jwt_token.php";
include_once "./verify_user.php";
include_once "./net_funcs.php";
include_once "./HTTP_STATES.php";

callFunctionWithMethod(
    #[
        Method(HTTPMethod::POST),
        Consumes(ContentType::APPLICATION_JSON),
        Produces(ContentType::APPLICATION_JSON)
    ]
    function ($input_data) {
        $data = $input_data["input"];
        if ($data !== null && $data["username"] !== null && $data["password"] !== null) {
            $username = $data["username"];
            $password = $data["password"];
            $user_id = verify_user($username, $password);
            return_as_json(generate_jwt_tokens($user_id));
        } else {
            status_exit(HTTP_STATES::BAD_REQUEST);
        }
    }
);

