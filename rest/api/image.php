<?php
include_once "./net_funcs.php";
include_once "./secure.php";
include_once "./connection.php";

$user = secure();

GET(function () {
    $url = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $parts = parse_url($url);
    parse_str($parts['query'], $query);

    if (isset($query["img_name"])) {
        list($id, $type) = explode(".", $query["img_name"]);
        $connection = get_connection();
        $statement = $connection->prepare("SELECT data FROM images WHERE id = ? AND type = ?");
        $statement->execute([$id, $type]);
        $result = $statement->get_result();

        $data = $result->fetch_assoc()["data"];
        if (!isset($data)) {
            status_exit(404);
        }
        $result->close();
        $statement->close();
        $connection->close();
        header("Cache-Control: public");
        header("Content-Type: image/*");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length:" . strlen($data));
        header("Content-Disposition: attachment; filename=" . $id . "." . $type);
        echo $data;
        status_exit(200);
    } else {
        status_exit(404);
    }

});

POST(function () {

});

DELETE(function () {

});
