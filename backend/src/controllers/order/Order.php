<?php


namespace order;

use oravix\HTTP\Consumes;
use oravix\HTTP\ContentType;
use oravix\HTTP\Controller;
use oravix\HTTP\HttpMethod;
use oravix\HTTP\input\FormData;
use oravix\HTTP\input\PathVariable;
use oravix\HTTP\Produces;
use oravix\HTTP\Request;
use oravix\security\Secure;

#[
    Controller("/order")
]
class Order {
    #[
        Request(
            "",
            HttpMethod::GET
        ),
        Consumes,
        Produces(ContentType::APPLICATION_JSON),
        Secure
    ]
    function getOrder(
        #[PathVariable("id", true)] string|null $id
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
        #[FormData] OrderUploadData $uploadData
    ) {

    }
}