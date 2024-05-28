<?php

namespace oravix\security\rest\api;

use oravix\HTTP\Consumes;
use oravix\HTTP\ContentType;
use oravix\HTTP\Controller;
use oravix\HTTP\HttpMethod;
use oravix\HTTP\input\Json;
use oravix\HTTP\Produces;
use oravix\HTTP\Request;
use oravix\security\rest\api\data\LoginData;
use oravix\security\rest\api\data\LogoutData;
use oravix\security\rest\api\data\RefreshTokenData;
use oravix\security\rest\api\data\RegisterData;

#[
    Controller("/security")
]
class Security {
    #[
        Request(
            "/register",
            HttpMethod::POST
        ),
        Consumes(ContentType::APPLICATION_JSON),
        Produces(ContentType::APPLICATION_JSON)
    ]
    function register(
        #[Json] RegisterData $data
    ) {

    }

    #[
        Request(
            "/login",
            HttpMethod::POST
        ),
        Consumes(ContentType::APPLICATION_JSON),
        Produces(ContentType::APPLICATION_JSON)
    ]
    function login(
        #[Json] LoginData $data
    ) {

    }

    #[
        Request(
            "/refresh-token",
            HttpMethod::POST
        ),
        Consumes(ContentType::APPLICATION_JSON),
        Produces(ContentType::APPLICATION_JSON)
    ]
    function refreshToken(
        #[Json] RefreshTokenData $data
    ) {

    }

    #[
        Request(
            "/logout",
            HttpMethod::POST
        ),
        Consumes(ContentType::APPLICATION_JSON)
    ]
    function logout(
        #[Json] LogoutData $data
    ) {

    }

}