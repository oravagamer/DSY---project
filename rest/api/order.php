<?php
include_once "./net_funcs.php";
include_once "./secure.php";
include_once "./DB.php";
include_once "./HTTP_STATES.php";


cancelWarns();

callFunctionWithMethod(
    #[
        Method(HTTPMethod::GET),
        Produces(ContentType::APPLICATION_JSON),
        Secure
    ]
    function ($input_data) {
        $url = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $parts = parse_url($url);
        parse_str($parts['query'], $query);
        if (key_exists("id", $input_data["path_params"])) {
            $database = new DB();
            $connection = $database->getConnection();
            $data = $connection->executeWithResponse('SELECT GROUP_CONCAT(images.id) AS img_id, shop_order.name AS name, shop_order.created_by AS cb, shop_order.created_for AS cf, shop_order.date_created AS dc, shop_order.finish_date AS fd, shop_order.status AS status, shop_order.description AS description FROM shop_order LEFT JOIN images ON images.order_id = shop_order.id WHERE shop_order.id = ? GROUP BY shop_order.id', [$input_data["path_params"]["id"]])[0];
            if (is_null($data)) {
                status_exit(HTTP_STATES::NOT_FOUND);
            }
            $return_data = [
                "order" => [
                    "name" => $data["name"],
                    "created_by" => $data["cb"],
                    "created_for" => $data["cf"],
                    "created_date" => $data["dc"],
                    "finish_date" => $data["fd"],
                    "status" => $data["status"],
                    "description" => $data["description"]
                ]
            ];

            if (isset($data["img_id"])) {
                $return_data["images"] = explode(",", $data["img_id"]);
            }

            return_as_json($return_data);
        }

    }
);

callFunctionWithMethod(
    #[
        Method(HTTPMethod::POST),
        Consumes(ContentType::MULTIPART_FORM_DATA),
        Produces(ContentType::APPLICATION_JSON),
        Secure
    ]
    function ($input_data) {
        $user = $input_data["user"];
        $input_data_form = $input_data["input"];
        if (isset($input_data_form["name"], $input_data_form["description"], $input_data_form["finish_date"])) {
            $name = $input_data_form["name"];
            $description = $input_data_form["description"];
            $for_user = $input_data_form["created_for"];
            $time = date("Y-m-d H:i:s", $input_data_form["finish_date"]);
            $database = new DB();
            $connection = $database->getConnection();

            $data = $connection->executeWithResponse('CALL create_order(?, ?, ?, ?, ?)', [$user["id"], $time, $name, $description, $for_user === "null" ? null : $for_user])[0];
            $order_id = $data["id"];
            $connection->closeStatement();

            if (isset($input_data_form['images'])) {
                $file_array_upload = [];
                for ($i = 0; $i < sizeof($input_data_form["images"]); $i++) {
                    $file_name = $input_data_form['images'][$i]["name"];
                    $dot_pos = strpos($file_name, ".") + 1;
                    array_push($file_array_upload, file_get_contents($input_data_form["images"][$i]["tmp_name"]), substr($file_name, $dot_pos, strlen($file_name) - $dot_pos), $order_id);

                }
                $connection->execute('INSERT INTO images(data, type, order_id) VALUES (?, ?, ?)' . str_repeat(", (?, ?, ?)", sizeof($input_data_form['images']) - 1), $file_array_upload);

            }
            $connection->closeConnection();
            return_as_json(["order_id" => $order_id]);
        } else {
            status_exit(HTTP_STATES::BAD_REQUEST);
        }

    }
);

callFunctionWithMethod(
    #[
        Method(HTTPMethod::PUT),
        Consumes(ContentType::APPLICATION_JSON),
        Secure
    ]
    function ($input_data) {
        $user = $input_data["user"];
        $id = $input_data["path_params"]["id"];

        $data = $input_data["input"];
        $is_admin = in_array("admin", $user["roles"]);

        if (isset($id) && isset($data)) {
            $name = $data["name"];
            $description = $data["description"];
            $due = $data["finish_date"];
            $created_for = $data["created_for"];
            $status = $data["status"];

            $database = new DB();
            $connection = $database->getConnection();
            $update_data = [];
            $sql_query = 'UPDATE shop_order SET';

            if (isset($name)) {
                $sql_query = $sql_query . " name = ?,";
                array_push($update_data, $name);
            }
            if (isset($description)) {
                $sql_query = $sql_query . " description = ?,";
                array_push($update_data, $description);
            }
            if (isset($due)) {
                $sql_query = $sql_query . " finish_date = ?,";
                array_push($update_data, date("Y-m-d H:i:s", $due));
            }
            if (isset($created_for)) {
                $sql_query = $sql_query . " created_for = ?,";
                array_push($update_data, $created_for === "null" ? null : $created_for);
            }
            if (isset($status)) {
                $sql_query = $sql_query . " status = ?,";
                array_push($update_data, $status);
            }
            array_push($update_data, $id);

            if ($sql_query[strlen($sql_query) - 1] === ",") {
                $str_split = str_split($sql_query);
                array_splice($str_split, strlen($sql_query) - 1, 1, "");
                $sql_query = implode("", $str_split);
                unset($str_split);
            }
            $sql_query = $sql_query . " WHERE id = ?";
            if (!$is_admin) {
                $sql_query = $sql_query . " AND created_by = ?";
                echo print_r($update_data, true);
                array_push($update_data, $user["id"]);
            }
            $connection->execute($sql_query, $update_data);

            if ($connection->getStatement()->affected_rows === 0) {
                status_exit(HTTP_STATES::NOT_FOUND);
            }
            $connection->closeConnection();
        }

    }
);

callFunctionWithMethod(
    #[
        Method(HTTPMethod::DELETE),
        Secure
    ]
    function ($input_data) {
        $user = $input_data["user"];
        $is_admin = in_array("admin", $user["roles"]);
        if (key_exists("id", $input_data["path_params"])) {
            $database = new DB();
            $connection = $database->getConnection();

            if ($is_admin) {
                $connection->execute('DELETE FROM shop_order WHERE id = ?', [$input_data["path_params"]["id"]]);
            } else {
                $connection->execute("DELETE FROM shop_order WHERE id = ? AND (shop_order.created_by = ? || shop_order.created_for = ?)", [$input_data["path_params"]["id"], $user["id"], $user["id"]]);
            }
            if ($connection->getStatement()->affected_rows === 0) {
                $connection->closeConnection();
                status_exit(HTTP_STATES::NOT_FOUND);
            }
            $connection->closeConnection();
        }

    }
);
