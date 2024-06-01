<?php


namespace order;

use oravix\HTTP\Consumes;
use oravix\HTTP\ContentType;
use oravix\HTTP\Controller;
use oravix\HTTP\HttpMethod;
use oravix\HTTP\input\Json;
use oravix\HTTP\input\multipart\FormData;
use oravix\HTTP\input\PathVariable;
use oravix\HTTP\Produces;
use oravix\HTTP\Request;
use oravix\security\Secure;
use oravix\security\SecurityUserId;
use PDO;

#[
    Controller("/order")
]
class Order {

    #[
        Request(
            "",
            HttpMethod::GET
        ),
        Produces(ContentType::APPLICATION_JSON),
        Secure
    ]
    function getOrder(
        #[PathVariable("id", true)] string $id
    ) {
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
        #[PathVariable("id", true)] string $id,
        #[Json] OrderPutJsonData           $editData,
        #[SecurityUserId] string           $userId
    ) {
    }

    #[
        Request(
            "",
            HttpMethod::DELETE
        ),
        Secure
    ]
    function removeOrder(
        #[PathVariable("id", true)] string $id,
        #[SecurityUserId] string           $userId
    ) {
    }
}