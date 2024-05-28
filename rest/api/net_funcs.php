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
    $namedAttributes = [];
    $user = null;
    $reflectionFunction = new ReflectionFunction($function);
    $attributes = $reflectionFunction->getAttributes();
    foreach ($attributes as $attribute) {
        $namedAttributes[$attribute->getName()] = $attribute;
    }
    if (!isset($namedAttributes[Method::class])) {
        throw new Exception("Please set required attributes!");
    }
    if ($namedAttributes[Method::class]->getArguments()[0]->value !== $_SERVER["REQUEST_METHOD"]) {
        return;
    }
    $contentType = apache_request_headers()["Content-Type"] ?? apache_request_headers()["content-type"];
    if (isset($namedAttributes[Secure::class])) {
        $user = secure();
    }
    try {
        $allowedContentType = $namedAttributes[Consumes::class]->getArguments()[0];
    } catch (Error $exception) {

    }

    if (!(str_contains($contentType, $allowedContentType->value)
        || (str_contains($allowedContentType->value, "/*")
            && explode( "/", $contentType)[0] === explode("/", $allowedContentType->value)[0])
        || (str_contains($allowedContentType->value, "*/")
            && explode("/", $contentType)[1] === explode("/", $allowedContentType->value)[1]))) {
        status_exit(HTTP_STATES::UNSUPPORTED_MEDIA_TYPE);
    }

    header('Content-Type: ' . (isset($namedAttributes[Produces::class]) ? $namedAttributes[Produces::class]->getArguments()[0]->value : ContentType::TEXT_PLAIN->value) . '; charset=utf-8');

    $url = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $url_parts = parse_url($url);
    parse_str($url_parts['query'], $url_parts_query);
    $input_value = null;
    if ($allowedContentType === ContentType::APPLICATION_JSON) {
        $input_value = json_decode(file_get_contents('php://input'), true);
    } else if ($allowedContentType === ContentType::TEXT_PLAIN) {
        $input_value = file_get_contents('php://input') ? file_get_contents('php://input') : null;
    } else if ($allowedContentType === ContentType::APPLICATION_OCTET_STREAM || $allowedContentType === ContentType::ALL_IMAGES) {
        $input_value = file_get_contents('php://input');
    } else if ($allowedContentType === ContentType::MULTIPART_FORM_DATA) {
        $input_value = $namedAttributes[Method::class]->getArguments()[0] === HTTPMethod::POST ? $_POST : $_GET;
        $filesKeys = array_keys($_FILES);
        foreach ($filesKeys as $filesKey) {
            if (is_array($_FILES[$filesKey])) {
                for ($j = 0; $j < sizeof($_FILES[$filesKey]["name"]); $j++) {
                    $fileKey = array_keys($_FILES[$filesKey]);
                    foreach ($fileKey as $key) {
                        $input_value[$filesKey][$j][$key] = $_FILES[$filesKey][$key][$j];
                    }
                }
            } else {
                $input_value[$filesKey][0] = $_FILES[$filesKey];
            }
        }
    }
    $function([
        "user" => $user,
        "path_params" => $url_parts_query,
        "input" => $input_value
    ]);
}

function return_as_json(array $data): void {
    echo json_encode($data);
    status_exit(HTTP_STATES::OK, "");
}

function cancelWarns(): void {
    error_reporting(E_ERROR | E_PARSE);
}