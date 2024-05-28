<?php

use order\OrderUploadData;
use rest\HTTP\Consumes;
use rest\HTTP\ContentType;
use rest\HTTP\HttpMethod;
use rest\HTTP\input\FormData;
use rest\HTTP\input\PathVariable;
use rest\HTTP\Method;
use rest\HTTP\Produces;
use rest\Path;
use rest\security\Secure;

#[
    Path("/order")
]
class Order {
    #[
        Path(""),
        Method(HttpMethod::GET),
        Consumes,
        Produces(ContentType::APPLICATION_JSON),
        Secure
    ]
    function getOrder(
        #[PathVariable("id", true)] string|null $id
    ) {
    }

    #[
        Path("j"),
        Method(HttpMethod::POST),
        Consumes(ContentType::MULTIPART_FORM_DATA),
        Produces(ContentType::APPLICATION_JSON),
        Secure
    ]
    function addOrder(
        #[FormData] OrderUploadData $uploadData
    ) {

    }
}