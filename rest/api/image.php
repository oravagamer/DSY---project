<?php
include_once "./net_funcs.php";
include_once "./secure.php";
include_once "./DB.php";
include_once "./HTTP_STATES.php";

$user = secure();

GET(function () {
    $url = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $parts = parse_url($url);
    parse_str($parts['query'], $query);

    if (isset($query["img_name"])) {
        list($id, $type) = explode(".", $query["img_name"]);
        $database = new DB();
        $connection = $database->getConnection();
        $db_data = $connection->executeWithResponse("SELECT data FROM images WHERE id = ? AND type = ?", [$id, $type])[0];
        $data = $db_data["data"];
        if (!isset($data)) {
            status_exit(HTTP_STATES::NOT_FOUND);
        }
        $connection->closeConnection();
        header("Cache-Control: public");
        header("Content-Type: image/*");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length:" . strlen($data));
        header("Content-Disposition: attachment; filename=" . $id . "." . $type);
        echo $data;
        status_exit(HTTP_STATES::OK);
    } else {
        status_exit(HTTP_STATES::NOT_FOUND);
    }

});

POST(function () {

});

DELETE(function () {

});
