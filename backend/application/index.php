<?php

use oravix\HTTP\ContentType;
use oravix\HTTP\Controller;
use oravix\HTTP\HTTPStates;
use oravix\HTTP\input\PathVariable;
use oravix\HTTP\Request;

require_once "./oravix/HTTP/input/FormData.php";
require_once "./oravix/HTTP/input/InputData.php";
require_once "./oravix/HTTP/input/Json.php";
require_once "./oravix/HTTP/input/JsonValue.php";
require_once "./oravix/HTTP/input/PathVariable.php";
require_once "./oravix/HTTP/Consumes.php";
require_once "./oravix/HTTP/ContentType.php";
require_once "./oravix/HTTP/Controller.php";
require_once "./oravix/HTTP/HttpMethod.php";
require_once "./oravix/HTTP/HttpStates.php";
require_once "./oravix/HTTP/Produces.php";
require_once "./oravix/HTTP/Request.php";
require_once "./oravix/security/RoleRestricted.php";
require_once "./oravix/security/Roles.php";
require_once "./oravix/security/Secure.php";
require_once "./settings.php";
require_once "./oravix/security/rest/api/Security.php";

$rootPath = "/DSY---project/backend";
$requestedUrl = explode($rootPath, $_SERVER["REQUEST_URI"])[1];
$pathParameters = "";
$settings = getSettings();

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
            if ($classAttribute->getName() === Controller::class) {
                foreach ($refClass->getMethods() as $method) {
                    foreach ($method->getAttributes() as $methodAttribute) {
                        if ($methodAttribute->getName() === Request::class) {
                            $paths[$methodAttribute->getArguments()[1]->value . ">" . $settings->application->path . $classAttribute->getArguments()[0] . $methodAttribute->getArguments()[0]] = $method;
                        }
                    }
                }
            }
        }
    } catch (Exception|Error $e) {
        header("Content-Type", ContentType::TEXT_PLAIN->value);
        statusExit(HttpStates::INTERNAL_SERVER_ERROR, $e->getMessage());
    }
}

$getPathDataKey = $_SERVER["REQUEST_METHOD"] . ">" . $requestedUrl;

if (isset($paths[$getPathDataKey])) {
    foreach ($paths[$getPathDataKey]->getAttributes() as $attribute) {
        $attribute->newInstance();

    }

    $methodPrams = [];
    foreach ($paths[$getPathDataKey]->getParameters() as $parameter) {

        foreach ($parameter->getAttributes() as $attribute) {

            if ($attribute->getName() === PathVariable::class) {
                if ($attribute->getArguments()[1] && !isset($urlPartsQuery[$attribute->getArguments()[0]])) {
                    statusExit(HttpStates::BAD_REQUEST);
                } else {
                    $methodPrams[] = isset($urlPartsQuery[$attribute->getArguments()[0]]) ? $urlPartsQuery[$attribute->getArguments()[0]] : null;
                };
            }
        }
    }

    echo $paths[$getPathDataKey]->invoke($paths[$getPathDataKey]->getDeclaringClass()->newInstance(), ...$methodPrams);
    statusExit(HttpStates::OK);

} else {
    statusExit(HTTPStates::NOT_FOUND);
}

function statusExit(HTTPStates $netStatus, ?string $message = null): void {
    echo $message === null ? ($netStatus === HttpStates::OK ? "" : $netStatus->name) : $message;
    http_response_code($netStatus->value);
    exit(0);
}
