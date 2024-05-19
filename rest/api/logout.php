<?php
include_once "./net_funcs.php";
include_once "./jwt_token.php";
include_once "./DB.php";
include_once "./HTTP_STATES.php";

callFunctionWithMethod(
    #[
        Method(HTTPMethod::POST),
        Consumes(ContentType::APPLICATION_JSON)
    ]
    function ($input_data) {
        if ($input_data["input"] !==null && $input_data["input"]["refresh"] !== null && $input_data["input"]["access"] !== null) {
            $old_access_token_data = decrypt_jwt_token($input_data["input"]["access"], true);
            $old_refresh_token_data = decrypt_jwt_token($input_data["input"]["refresh"], false);

            if ($old_refresh_token_data["payload"]["exp"] > time() && $old_refresh_token_data["payload"]["aud"] === $old_access_token_data["payload"]["aud"]) {
                $database = new DB();
                $connection = $database->getConnection();
                $db_data = $connection->executeWithResponse("SELECT count(*) AS count FROM session WHERE id = ? AND status = TRUE", [$old_refresh_token_data["payload"]["aud"]])[0];

                $connection->closeStatement();
                if ($db_data["count"] !== 1) {
                    status_exit(HTTP_STATES::FORBIDDEN);
                }


                $connection->execute("UPDATE session SET status = FALSE WHERE id = ?", [$old_refresh_token_data["payload"]["aud"]]);
                $connection->closeConnection();

                redirect("/");
                status_exit(HTTP_STATES::OK);
            } else {
                status_exit(HTTP_STATES::FORBIDDEN);
            }

        } else {
            status_exit(HTTP_STATES::BAD_REQUEST);
        }
    }
);
