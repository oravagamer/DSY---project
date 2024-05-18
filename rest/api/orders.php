<?php
include_once "./net_funcs.php";
include_once "./secure.php";
include_once "./connection.php";

$user = secure();
GET(function () {
    $url = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $parts = parse_url($url);
    parse_str($parts['query'], $query);
    if (!isset($query["sort_by"])) {
        try {
            $connection = get_connection();
            $statement = $connection->prepare('SELECT shop_order.id AS id, shop_order.name AS name, shop_order.created_by AS created_by, shop_order.created_for AS created_for, shop_order.date_created AS created_date, shop_order.finish_date AS finish_date, shop_order.status AS status, shop_order.description AS description FROM shop_order');
            $statement->execute();
            $result = $statement->get_result();
            $return_data = [];
            while ($hash = $result->fetch_assoc()) {
                array_push($return_data, $hash);
            }

            return_as_json($return_data);
        } catch (Exception $exception) {
            status_exit(500);
        }
    }
});
