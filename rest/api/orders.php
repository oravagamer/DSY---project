<?php
include_once "./net_funcs.php";
include_once "./secure.php";
include_once "./DB.php";
include_once "./HTTP_STATES.php";

callFunctionWithMethod(
    #[
        Method(HTTPMethod::GET),
        Produces(ContentType::APPLICATION_JSON),
        Secure
    ]
    function ($input_data) {
        if (!isset($input_data["path_param"]["sort_by"])) {
            $database = new DB();
            $connection = $database->getConnection();
            $return_data = $connection->executeWithResponse('SELECT shop_order.id AS id, shop_order.name AS name, shop_order.created_by AS created_by, shop_order.created_for AS created_for, shop_order.date_created AS created_date, shop_order.finish_date AS finish_date, shop_order.status AS status, shop_order.description AS description FROM shop_order');
            $connection->closeConnection();

            return_as_json($return_data);
        }
    }
);
