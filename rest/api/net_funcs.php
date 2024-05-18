<?php
include_once "./HTTP_STATES.php";
include_once "./ContentType.php";
include_once "./HTTPMethod.php";
include_once "./secure.php";

function redirect(string $url): void {
    header("Location: " . $url);
}

function status_exit(HTTP_STATES $net_status, ?string $message = null): void {
    echo $message === null ? $net_status->name : $message;
    http_response_code($net_status->value);
    exit(0);
}

function GET($function): void {
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        $function();
    }
}

function PUT($function): void {
    if ($_SERVER["REQUEST_METHOD"] === "PUT") {
        $function();
    }
}

function POST($function): void {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $function();
    }
}

function DELETE($function): void {
    if ($_SERVER["REQUEST_METHOD"] === "DELETE") {
        $function();
    }
}

function callFunctionWithMethod($function): void {
    $method = null;
    $consumes = null;
    $produces = null;
    $secure = null;
    $user = null;
    $reflectionFunction = new ReflectionFunction($function);
    $attributes = $reflectionFunction->getAttributes();
    foreach ($attributes as $attribute) {
        switch ($attribute->getName()) {
            case "Method":
            {
                $method = $attribute;
                break;
            }
            case "Consumes":
            {
                $consumes = $attribute;
                break;
            }
            case "Produces":
            {
                $produces = $attribute;
                break;
            }
            case "Secure":
            {
                $secure = $attribute;
                break;
            }
        }
    }
    if (!isset($method, $produces)) {
        throw new Exception("Please set required attributes!");
    }
    $contentType = apache_request_headers()["Content-Type"];
    if (isset($secure)) {
        $user = secure();
    }
    try {
        $allowedContentType = $consumes->getArguments()[0];
    } catch (Error $exception) {

    }
    $responseType = $produces->getArguments()[0];

    if (!(str_contains($contentType, $allowedContentType->value)
        || (str_contains($allowedContentType->value, "/*")
            && str_contains($contentType, str_split($allowedContentType->value, "/")[0]))
        || (str_contains($allowedContentType->value, "*/")
            && str_contains($contentType, str_split($allowedContentType->value, "/")[1])))) {
        status_exit(HTTP_STATES::UNSUPPORTED_MEDIA_TYPE);
    }

    header('Content-Type: ' . $responseType->value . '; charset=utf-8');

    if ($method->getArguments()[0]->value === $_SERVER["REQUEST_METHOD"]) {
        $function([
            "user" => $user
        ]);
    }
}

#[Attribute(Attribute::TARGET_FUNCTION)]
final class Method {
    public function __construct(HTTPMethod $name) {

    }
}

#[Attribute(Attribute::TARGET_FUNCTION)]
final class Consumes {
    public function __construct(ContentType $contentType) {

    }
}

#[Attribute(Attribute::TARGET_FUNCTION)]
final class Produces {
    public function __construct(ContentType $contentType) {

    }
}

#[Attribute(Attribute::TARGET_FUNCTION)]
final class Secure {
    public function __construct() {

    }
}

function return_as_json(array $data): void {
    echo json_encode($data);
    status_exit(HTTP_STATES::OK, "");
}

function cancelWarns(): void {
    error_reporting(E_ERROR | E_PARSE);
}