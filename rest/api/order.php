<?php
include_once "./net_funcs.php";
include_once "./secure.php";
include_once "./connection.php";

cacncelWarns();
$user = secure();

GET(function () {
    $url = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $parts = parse_url($url);
    parse_str($parts['query'], $query);
    if (key_exists("id", $query)) {
        try {
            $connection = get_connection();
            $statement = $connection->prepare('SELECT images.id AS img_id, images.type AS img_type, shop_order.name AS name, shop_order.created_by AS cb, shop_order.created_for AS cf, shop_order.date_created AS dc, shop_order.finish_date AS fd, shop_order.status AS status, shop_order.description AS description FROM images RIGHT JOIN shop_order ON shop_order.id = images.order_id WHERE shop_order.id = ?');
            $statement->execute([$query["id"]]);
            $result = $statement->get_result();
            $hash = $result->fetch_assoc();
            if (is_null($hash)) {
                status_exit(404);
            }
            $return_data = [
                "order" => [
                    "name" => $hash["name"],
                    "created_by" => $hash["cb"],
                    "created_for" => $hash["cf"],
                    "created_date" => $hash["dc"],
                    "finish_date" => $hash["fd"],
                    "status" => $hash["status"],
                    "description" => $hash["description"]
                ],
                "images" => []
            ];

            do {
                array_push($return_data["images"], $hash["img_id"] . "." . $hash["img_type"]);
            } while ($hash = $result->fetch_assoc());
            return_as_json($return_data);
        } catch (Exception $exception) {
            status_exit(500);
        }
    }

});

POST(function () {
    global $user;
    if (isset($_POST["name"], $_POST["description"], $_POST["finish_date"])) {
        try {
            $name = $_POST["name"];
            $description = $_POST["description"];
            $for_user = $_POST["created_for"];
            $time = date("Y-m-d H:i:s", $_POST["finish_date"]);
            $connection = get_connection();

            $statement = $connection->prepare('CALL create_order(?, ?, ?, ?, ' . ($for_user === null ? "NULL" : mysqli_escape_string($connection, $for_user)). ")");
            $statement->execute([$user["id"], $time, $name, $description]);
            $result = $statement->get_result();

            $hash = $result->fetch_assoc();
            $order_id = $hash["id"];
            $result->close();
            $statement->close();

            if (isset($_FILES['images'])) {
                $file_array_upload = [];
                $file_count = is_array($_FILES["images"]["name"]) ? sizeof($_FILES['images']["name"]) : 1;
                $statement = $connection->prepare('INSERT INTO images(data, type, order_id) VALUES (?, ?, ?)' . str_repeat(", (?, ?, ?)", is_array($_FILES['images']) ? sizeof($_FILES['images']["name"]) - 1 : 0));
                for ($i = 0; $i <= $file_count - 1; $i++) {
                    $file_name = $_FILES['images']["name"][$i];
                    $dot_pos = strpos($file_name, ".") + 1;
                    array_push($file_array_upload, file_get_contents($_FILES["images"]["tmp_name"][$i]), substr($file_name, $dot_pos, strlen($file_name) - $dot_pos), $order_id);

                }
                $statement->execute($file_array_upload);

            }
            $connection->close();
            return_as_json(["order_id" => $order_id]);
        } catch (Exception $exception) {
            echo $exception->getMessage();
            status_exit(500);
        }
    } else {
        status_exit(400);
    }

});

PUT(function () {
    global $user;
    $url = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $parts = parse_url($url);
    parse_str($parts['query'], $query);
    $id = $query["id"];

    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);
    $is_admin = in_array("admin", $user["roles"]);

    if (key_exists("id", $query) && isset($data)) {
        $name = $data["name"];
        $description = $data["description"];
        $due = $data["finish_date"];
        $created_for = $data["created_for"];
        $status = $data["status"];

        try {
            $connection = get_connection();
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
                array_push($update_data, $created_for);
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
                $sql_query = $sql_query . " AND (created_by = ? OR created_by = ?)";
                array_push($update_data, $user["id"], $user["id"]);
            }
            $statement = $connection->prepare($sql_query);
            $statement->execute($update_data);

            if ($statement->affected_rows === 0) {
                status_exit(404);
            }
            $statement->close();
            $connection->close();
        } catch (Exception $exception) {
            echo $exception->getMessage();
            status_exit(500);
        }
    }

});

DELETE(function () {
    global $user;
    $url = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $parts = parse_url($url);
    parse_str($parts['query'], $query);
    $is_admin = in_array("admin", $user["roles"]);
    if (key_exists("id", $query)) {
        try {
            $connection = get_connection();
            $statement = $connection->prepare('DELETE FROM shop_order WHERE id = ?' . ($is_admin ? "" : " AND (shop_order.created_by = ? || shop_order.created_for = ?)"));

            if ($is_admin) {
                $statement->execute([$query["id"]]);
            } else {
                $statement->execute([$query["id"], $user["id"], $user["id"]]);
            }
            if ($statement->affected_rows === 0) {
                $statement->close();
                $connection->close();
                status_exit(404);
            }
        } catch (Exception $exception) {
            status_exit(500);
        }
    }

});
