<?php

namespace image;

use oravix\db\Database;
use oravix\HTTP\ContentType;
use oravix\HTTP\Controller;
use oravix\HTTP\HttpHeader;
use oravix\HTTP\HttpMethod;
use oravix\HTTP\HttpResponse;
use oravix\HTTP\input\PathVariable;
use oravix\HTTP\Produces;
use oravix\HTTP\Request;
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
        #[PathVariable("id", true)] $id
    ) {
        $connection = $this->database->getConnection();
        $statement = $connection->prepare('SELECT data, type FROM images WHERE id = :order_id');
        $statement->execute([
            "order_id" => $id
        ]);
        $statement->setFetchMode(PDO::FETCH_NAMED);
        $data = $statement->fetch();

        return new HttpResponse($data["data"], headers: [
            new HttpHeader("Cache-Control", "public"),
            new HttpHeader("Content-Transfer-Encoding", "Binary"),
            new HttpHeader("Content-Length", strlen($data["data"])),
            new HttpHeader("Content-Disposition", "attachment; filename=" . $id . "." . $data["type"])
        ]);
    }
}