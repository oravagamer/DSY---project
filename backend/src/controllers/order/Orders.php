<?php

namespace order;

use oravix\db\Database;
use oravix\exceptions\HttpException;
use oravix\HTTP\Consumes;
use oravix\HTTP\ContentType;
use oravix\HTTP\Controller;
use oravix\HTTP\HttpMethod;
use oravix\HTTP\HttpResponse;
use oravix\HTTP\HttpStates;
use oravix\HTTP\input\PathVariable;
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
    function getOrders(
        #[PathVariable("page", true)] int        $page,
        #[PathVariable("count", true)] int       $rowsPerPage,
        #[PathVariable("sort-by", false)] string $sortBy = "date_created",
        #[PathVariable("asc", false)] bool       $ascending = false
    ) {
        $connection = (new Database())->getConnection();
        if (!in_array($sortBy, array("name", "date_created", "finish_date", "status", "id", "created_by", "created_for"))) {
            throw new HttpException(HttpStates::BAD_REQUEST, "Please use existing column");
        }
        $statement = $connection->prepare(sprintf('SELECT shop_order.id AS id, shop_order.name AS name, shop_order.created_by AS created_by, shop_order.created_for AS created_for, shop_order.date_created AS created_date, shop_order.finish_date AS finish_date, shop_order.status AS status, shop_order.description AS description FROM shop_order ORDER BY %s %s LIMIT :number_of_records OFFSET :start_index', $sortBy, $ascending ? "ASC" : "DESC"));
        $statement->setFetchMode(PDO::FETCH_NAMED);
        $statement->execute([
            "start_index" => $page * $rowsPerPage,
            "number_of_records" => $rowsPerPage
        ]);
        $data = $statement->fetchAll();
        $statement = $connection->prepare('SELECT COUNT(*) as count FROM shop_order');
        $statement->execute();
        $count = $statement->fetch()["count"];
        return new HttpResponse([
            "count" => $count,
            "orders" => !$data ? [] : $data
        ]);
    }
}