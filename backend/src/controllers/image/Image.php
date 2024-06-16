<?php

namespace image;

use oravix\db\Database;
use oravix\HTTP\Consumes;
use oravix\HTTP\ContentType;
use oravix\HTTP\Controller;
use oravix\HTTP\HttpHeader;
use oravix\HTTP\HttpMethod;
use oravix\HTTP\HttpResponse;
use oravix\HTTP\HttpStates;
use oravix\HTTP\input\FileUpload;
use oravix\HTTP\input\PathVariable;
use oravix\HTTP\Produces;
use oravix\HTTP\Request;
use oravix\security\Secure;
use PDO;

#[
    Controller("/image")
]
class Image {
    private Database $database;

    public function __construct() {
        $this->database = new Database();
    }

    #[
        Request(
            "",
            HttpMethod::GET
        ),
        Produces(ContentType::ALL_IMAGES)
    ]
    public function downloadImage(
        #[PathVariable("id", true)] $imageId
    ) {
        $connection = $this->database->getConnection();
        $statement = $connection->prepare('SELECT data, type FROM images WHERE id = :image_id');
        $statement->execute([
            "image_id" => $imageId
        ]);
        $statement->setFetchMode(PDO::FETCH_NAMED);
        $data = $statement->fetch();

        return new HttpResponse($data["data"], headers: [
            new HttpHeader("Cache-Control", "public"),
            new HttpHeader("Content-Transfer-Encoding", "Binary"),
            new HttpHeader("Content-Length", strlen($data["data"])),
            new HttpHeader("Content-Disposition", "attachment; filename=" . $imageId . "." . $data["type"])
        ]);
    }

    #[
        Request(
            "",
            HttpMethod::POST
        ),
        Consumes(ContentType::ALL_IMAGES),
        Secure
    ]
    public function uploadImage(
        #[FileUpload] string                 $image,
        #[PathVariable("id", true)] string   $orderId,
        #[PathVariable("type", true)] string $fileType
    ) {
        $connection = $this->database->getConnection();
        $statement = $connection->prepare('INSERT INTO images(data, type, order_id) VALUES (:data, :type, :order_id)');
        $statement->execute([
            "data" => $image,
            "type" => $fileType,
            "order_id" => $orderId
        ]);
    }

    #[
        Request(
            "",
            HttpMethod::DELETE
        ),
        Secure
    ]
    public function deleteImage(
        #[PathVariable("id", true)] string $imageId
    ) {
        $connection = $this->database->getConnection();
        $statement = $connection->prepare("DELETE FROM images WHERE id = :image_id");
        $statement->execute([
            "image_id" => $imageId,
        ]);
        if ($statement->rowCount() === 0) {
            return new HttpResponse(status: HttpStates::NOT_FOUND);
        }
    }
}