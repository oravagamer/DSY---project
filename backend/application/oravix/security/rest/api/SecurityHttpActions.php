<?php

namespace oravix\security\rest\api;

use oravix\HTTP\Consumes;
use oravix\HTTP\ContentType;
use oravix\HTTP\Controller;
use oravix\HTTP\HttpMethod;
use oravix\HTTP\HttpResponse;
use oravix\HTTP\HttpStates;
use oravix\HTTP\input\Json;
use oravix\HTTP\Produces;
use oravix\HTTP\Request;
use oravix\security\JOSE\Algorithm;
use oravix\security\JOSE\JWA;
use oravix\security\JOSE\JWT;
use oravix\security\JOSE\Security;
use oravix\security\rest\api\data\LoginData;
use oravix\security\rest\api\data\LogoutData;
use oravix\security\rest\api\data\TokensData;
use oravix\security\rest\api\data\RegisterData;
use oravix\security\SecurityUserId;

#[
    Controller("/security")
]
class SecurityHttpActions {

    #[
        Request(
            "/register",
            HttpMethod::POST
        ),
        Consumes(ContentType::APPLICATION_JSON),
        Produces(ContentType::APPLICATION_JSON)
    ]
    function register(
        #[Json] RegisterData     $data,
        #[SecurityUserId] Security $security
    ): void {
        $security->register($data);
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
        #[Json] LoginData        $data,
        #[SecurityUserId] Security $security
    ): HttpResponse {
        $tokens = $security->login($data);
        return new HttpResponse(
            HttpStates::OK,
            [
                "access" => $tokens["access"]->getJWTString(),
                "refresh" => $tokens["refresh"]->getJWTString()
            ]);
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
        #[Json] TokensData       $data,
        #[SecurityUserId] Security $security
    ): HttpResponse {
        $tokens = $security->refreshToken($data);
        return new HttpResponse(
            HttpStates::OK,
            [
                "access" => $tokens["access"]->getJWTString(),
                "refresh" => $tokens["refresh"]->getJWTString()
            ]);
    }

    #[
        Request(
            "/logout",
            HttpMethod::POST
        ),
        Consumes(ContentType::APPLICATION_JSON)
    ]
    function logout(
        #[Json] TokensData       $data,
        #[SecurityUserId] Security $security
    ): void {
        $security->logout($data);
    }

}