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
use oravix\HTTP\input\PageInput;
use oravix\HTTP\input\PageInputParams;
use oravix\HTTP\input\PathVariable;
use oravix\HTTP\Produces;
use oravix\HTTP\Request;
use oravix\security\Secure;
use oravix\security\SecurityUserId;
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
        #[PageInputParams("date_created", array("name", "date_created", "finish_date", "status", "id", "created_by", "created_for"))] PageInput $pageInput,
        #[SecurityUserId] string                                                                                                                $userId,
        #[PathVariable("completed", false)] bool                                                                                                $completed = false,
        #[PathVariable("only-my", false)] bool                                                                                                  $onlyMy = false
    ) {
        $connection = (new Database())->getConnection();
        $search = "";
        if (!$completed && $onlyMy) {
            $search .= sprintf("WHERE (status != 2 OR status IS NULL) AND (created_by = '%s' OR created_for = '%s')", $userId, $userId);
        } elseif (!$completed) {
            $search .= "WHERE status != 2 OR status IS NULL";
        } elseif ($onlyMy) {
            $search .= sprintf("WHERE created_by = '%s' OR created_for = '%s'", $userId, $userId);
        }
        $query = sprintf('SELECT shop_order.id AS id, shop_order.name AS name, shop_order.created_by AS created_by, shop_order.created_for AS created_for, shop_order.date_created AS created_date, shop_order.finish_date AS finish_date, shop_order.status AS status, shop_order.description AS description FROM shop_order %s ORDER BY %s %s LIMIT :number_of_records OFFSET :start_index', $search, $pageInput->sortBy, $pageInput->ascending ? "ASC" : "DESC");
        $statement = $connection->prepare($query);
        $statement->setFetchMode(PDO::FETCH_NAMED);
        $statement->execute([
            "start_index" => $pageInput->page * $pageInput->rowsPerPage,
            "number_of_records" => $pageInput->rowsPerPage
        ]);
        $data = $statement->fetchAll();
        $statement = $connection->prepare(sprintf('SELECT COUNT(*) as count FROM shop_order %s', $search));
        $statement->execute();
        $count = $statement->fetch()["count"];
        return new HttpResponse([
            "count" => $count,
            "orders" => !$data ? [] : $data
        ]);
    }
}