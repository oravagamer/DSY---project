<?php

use rest\HTTP\ContentType;
use rest\HTTP\HTTPStates;
use rest\HTTP\input\PathVariable;
use rest\Path;

require_once "./rest/HTTP/input/FormData.php";
require_once "./rest/HTTP/input/InputData.php";
require_once "./rest/HTTP/input/Json.php";
require_once "./rest/HTTP/input/JsonValue.php";
require_once "./rest/HTTP/input/PathVariable.php";
require_once "./rest/HTTP/Consumes.php";
require_once "./rest/HTTP/ContentType.php";
require_once "./rest/HTTP/HttpMethod.php";
require_once "./rest/HTTP/HttpStates.php";
require_once "./rest/HTTP/Method.php";
require_once "./rest/HTTP/Produces.php";
require_once "./rest/security/RoleRestricted.php";
require_once "./rest/security/Roles.php";
require_once "./rest/security/Secure.php";
require_once "./rest/Path.php";

$rootPath = "/DSY---project/backend";
$requestedUrl = explode($rootPath, $_SERVER["REQUEST_URI"])[1];
$pathParameters = "";

if (str_contains($requestedUrl, "/?")) {
    list($requestedUrl, $pathParameters) = explode("/?", $requestedUrl, 2);
} elseif (str_contains($requestedUrl, "?")) {
    list($requestedUrl, $pathParameters) = explode("?", $requestedUrl, 2);
}

if ($requestedUrl[strlen($requestedUrl) - 1] === "/") {
    $requestedUrl = substr($requestedUrl, 0, strlen($requestedUrl) - 1);
}

parse_str(parse_url("http://test/?" . $pathParameters)["query"], $urlPartsQuery);


$searchPath = "../src/*";
$searchedDirs = glob($searchPath);
$searchedFiles = glob($searchPath . ".php");
while (sizeof($searchedFiles) !== 0 || sizeof($searchedDirs) !== 0) {
    if (sizeof($searchedFiles) !== 0) {
        foreach ($searchedFiles as $file) {
            require $file;
        }
    }
    $searchPath = $searchPath . "/*";
    $searchedDirs = glob($searchPath);
    $searchedFiles = glob($searchPath . ".php");
}

$paths = [];

foreach (get_declared_classes() as $class) {
    try {
        $refClass = new ReflectionClass($class);

        foreach ($refClass->getAttributes() as $classAttribute) {
            if ($classAttribute->getName() === Path::class) {
                foreach ($refClass->getMethods() as $method) {
                    foreach ($method->getAttributes() as $methodAttribute) {
                        if ($methodAttribute->getName() === Path::class) {
                            $paths[$classAttribute->getArguments()[0] . $methodAttribute->getArguments()[0]] = $method;
                        }
                    }
                }
            }
        }
    } catch (Exception|Error $e) {
        header("Content-Type", ContentType::TEXT_PLAIN->value);
        status_exit(HttpStates::INTERNAL_SERVER_ERROR, $e->getMessage());
    }
}

if (isset($paths[$requestedUrl])) {
    foreach ($paths[$requestedUrl]->getAttributes() as $attribute) {
        $attribute->newInstance();

    }

    $methodPrams = [];
    foreach ($paths[$requestedUrl]->getParameters() as $parameter) {

        foreach ($parameter->getAttributes() as $attribute) {

            if ($attribute->getName() === PathVariable::class) {
                if ($attribute->getArguments()[1] && !isset($urlPartsQuery[$attribute->getArguments()[0]])) {
                    status_exit(HttpStates::BAD_REQUEST);
                } else {
                    $methodPrams[] = isset($urlPartsQuery[$attribute->getArguments()[0]]) ? $urlPartsQuery[$attribute->getArguments()[0]] : null;
                };
            }
        }
    }

    echo $paths[$requestedUrl]->invoke($paths[$requestedUrl]->getDeclaringClass()->newInstance(), ...$methodPrams);
    status_exit(HttpStates::OK);

} else {
    status_exit(HTTPStates::NOT_FOUND);
}

function status_exit(HTTPStates $netStatus, ?string $message = null): void {
    echo $message === null ? ($netStatus === HttpStates::OK ? "" : $netStatus->name) : $message;
    http_response_code($netStatus->value);
    exit(0);
}
