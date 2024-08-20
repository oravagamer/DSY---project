<?php


namespace order;

use oravix\db\Database;
use oravix\HTTP\Consumes;
use oravix\HTTP\ContentType;
use oravix\HTTP\Controller;
use oravix\HTTP\HttpMethod;
use oravix\HTTP\HttpResponse;
use oravix\HTTP\HttpStates;
use oravix\HTTP\input\Json;
use oravix\HTTP\input\multipart\FormData;
use oravix\HTTP\input\PathVariable;
use oravix\HTTP\Produces;
use oravix\HTTP\Request;
use oravix\security\Secure;
use oravix\security\SecurityUserId;
use PDO;
use PDOException;

#[
    Controller("/order")
]
class Order {
    private Database $database;

    public function __construct() {
        $this->database = new Database();
    }

    #[
        Request(
            "",
            HttpMethod::GET
        ),
        Produces(ContentType::APPLICATION_JSON),
        Secure
    ]
    function getOrder(
        #[PathVariable("id", true)] string $orderId
    ) {
        $connection = $this->database->getConnection();
        $statement = $connection->prepare("SELECT GROUP_CONCAT(images.id) AS img_id, shop_order.name AS name, shop_order.created_by AS cb, shop_order.created_for AS cf, shop_order.date_created AS dc, shop_order.finish_date AS fd, shop_order.status AS status, shop_order.description AS description FROM shop_order LEFT JOIN images ON images.order_id = shop_order.id WHERE shop_order.id = :order_id GROUP BY shop_order.id");
        $statement->execute([
            "order_id" => $orderId
        ]);
        $statement->setFetchMode(PDO::FETCH_NAMED);
        $data = $statement->fetch();
        $images = explode(",", $data["img_id"]);

        return new HttpResponse([
            "order" => [
                "name" => $data["name"],
                "created_by" => $data["cb"],
                "created_for" => $data["cf"],
                "created_date" => $data["dc"],
                "finish_date" => $data["fd"],
                "status" => $data["status"],
                "description" => $data["description"]
            ],
            "images" => $images[0] === "" ? [] : $images
        ]);
    }

    #[
        Request(
            "",
            HttpMethod::POST
        ),
        Consumes(ContentType::MULTIPART_FORM_DATA),
        Produces(ContentType::APPLICATION_JSON),
        Secure
    ]
    function addOrder(
        #[FormData] OrderUploadData $uploadData,
        #[SecurityUserId] string    $userId
    ) {
        $connection = $this->database->getConnection();
        $statement = $connection->prepare('CALL create_order(:user_id, :finish_date, :name, :desc, :for)');
        try {
            $statement->execute([
                "user_id" => $userId,
                "finish_date" => date("Y-m-d H:i:s", $uploadData->finishDate),
                "name" => $uploadData->name,
                "desc" => $uploadData->description,
                "for" => $uploadData->createdFor
            ]);
        } catch (PDOException $exception) {
            return new HttpResponse(status: $exception->getCode() == 23000 ? HttpStates::CONFLICT : HttpStates::INTERNAL_SERVER_ERROR);
        }
        $statement->setFetchMode(PDO::FETCH_NAMED);
        $data["order_id"] = $statement->fetch()["id"];
        $statement = null;

        if (isset($uploadData->images)) {
            $uploadFiles = [];
            foreach ($uploadData->images as $image) {
                $dotPos = strpos($image->name, ".") + 1;
                array_push($uploadFiles, file_get_contents($image->tpmName), substr($image->name, $dotPos, strlen($image->name) - $dotPos), $data["order_id"]);
            }
            $statement = $connection->prepare('INSERT INTO images(data, type, order_id) VALUES (?, ?, ?)' . str_repeat(", (?, ?, ?)", sizeof($uploadData->images) - 1));
            $statement->execute($uploadFiles);
        }

        return new HttpResponse($data);
    }

    #[
        Request(
            "",
            HttpMethod::PUT
        ),
        Consumes(ContentType::APPLICATION_JSON),
        Secure
    ]
    function updateOrder(
        #[PathVariable("id", true)] string $orderId,
        #[Json] OrderPutJsonData           $editData
    ) {
        $connection = $this->database->getConnection();
        $statement = $connection->prepare('UPDATE shop_order SET  name = :name,  description = :desc, finish_date = :finish_date, created_for = :cf, status = :status WHERE id = :order_id');
        try {
            $statement->execute([
                "name" => $editData->name,
                "desc" => $editData->description,
                "finish_date" => date("Y-m-d H:i:s", $editData->finishDate),
                "cf" => $editData->createdFor,
                "status" => $editData->status,
                "order_id" => $orderId
            ]);
        } catch (PDOException $exception) {
            return new HttpResponse(status: $exception->getCode() == 23000 ? HttpStates::CONFLICT : HttpStates::INTERNAL_SERVER_ERROR);
        }
    }

    #[
        Request(
            "",
            HttpMethod::DELETE
        ),
        Secure
    ]
    function removeOrder(
        #[PathVariable("id", true)] string $orderId
    ) {
        $connection = $this->database->getConnection();
        $statement = $connection->prepare('DELETE FROM shop_order WHERE id = :order_id');
        $statement->execute([
            "order_id" => $orderId
        ]);
        if ($statement->rowCount() === 0) {
            return new HttpResponse(status: HttpStates::NOT_FOUND);
        }
    }
}