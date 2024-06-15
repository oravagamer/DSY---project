<?php

namespace order;

use oravix\db\Database;
use oravix\HTTP\Consumes;
use oravix\HTTP\ContentType;
use oravix\HTTP\Controller;
use oravix\HTTP\HttpMethod;
use oravix\HTTP\HttpResponse;
use oravix\HTTP\HttpStates;
use oravix\HTTP\Produces;
use oravix\HTTP\Request;
use oravix\security\Secure;
use PDO;

#[
    Controller("/orders")
]
class Orders {
    #[
        Request(
            "",
            HttpMethod::GET
        ),
        Produces(ContentType::APPLICATION_JSON),
        Secure
    ]
    function getOrders() {
        $connection = (new Database())->getConnection();
        $statement = $connection->prepare('SELECT shop_order.id AS id, shop_order.name AS name, shop_order.created_by AS created_by, shop_order.created_for AS created_for, shop_order.date_created AS created_date, shop_order.finish_date AS finish_date, shop_order.status AS status, shop_order.description AS description FROM shop_order');
        $statement->setFetchMode(PDO::FETCH_NAMED);
        $statement->execute();
        $data = $statement->fetchAll();
        return new HttpResponse(!$data ? [] : $data);
    }
}