<?php
include_once "./net_funcs.php";
include_once "./secure.php";
include_once "./DB.php";
include_once "./HTTP_STATES.php";

callFunctionWithMethod(
    #[
        Method(HTTPMethod::GET),
        Produces(ContentType::ALL_IMAGES)
    ]
    function ($input_data) {
        $img_data = $input_data["path_params"];

        if (isset($img_data ["id"])) {
            $id = $img_data["id"];
            $database = new DB();
            $connection = $database->getConnection();
            $db_data = $connection->executeWithResponse("SELECT data, type FROM images WHERE id = ?", [$id])[0];
            $data = $db_data["data"];
            $type = $db_data["type"];
            if (!isset($data)) {
                status_exit(HTTP_STATES::NOT_FOUND);
            }
            $connection->closeConnection();
            header("Cache-Control: public");
            header("Content-Transfer-Encoding: Binary");
            header("Content-Length:" . strlen($data));
            header("Content-Disposition: attachment; filename=" . $id . "." . $type);
            echo $data;
            status_exit(HTTP_STATES::OK);
        } else {
            status_exit(HTTP_STATES::NOT_FOUND);
        }

    }
);

callFunctionWithMethod(
    #[
        Method(HTTPMethod::POST),
        Consumes(ContentType::ALL_IMAGES),
        Secure
    ]
    function ($input_data) {
        if (isset($input_data["path_params"]["id"], $input_data["path_params"]["type"]) && $input_data["input"] !== null) {
            $id = $input_data["path_params"]["id"];
            $type = $input_data["path_params"]["type"];
            $database = new DB();
            $connection = $database->getConnection();
            $connection->execute("INSERT INTO images(data, type, order_id) VALUES (?, ?, ?)", [$input_data["input"], $type, $id]);
            $connection->closeConnection();
        } else {
            status_exit(HTTP_STATES::BAD_REQUEST);
        }
    }
);

callFunctionWithMethod(
    #[
        Method(HTTPMethod::DELETE),
        Secure
    ]
    function ($input_data) {
        if (isset($input_data["path_params"]["id"])) {
            $id = $input_data["path_params"]["id"];
            $database = new DB();
            $connection = $database->getConnection();
            $connection->execute("DELETE FROM images WHERE id = ?", [$id]);
            if ($connection->getStatement()->affected_rows !== 1) {
                status_exit(HTTP_STATES::NOT_FOUND);
            }
            $connection->closeConnection();
        } else {
            status_exit(HTTP_STATES::BAD_REQUEST);
        }
    }
);