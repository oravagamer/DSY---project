<?php
include_once "./HTTP_STATES.php";
include_once "./ContentType.php";

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

function return_as_json(array $data): void {
    setResponseType(ContentType::APPLICATION_JSON);
    echo json_encode($data);
    status_exit(HTTP_STATES::OK, "");
}

function cancelWarns(): void {
    error_reporting(E_ERROR | E_PARSE);
}

function setResponseType(ContentType $type): void {
    header('Content-Type: ' . $type->value . '; charset=utf-8');
}

function checkContentType(ContentType $type): void {
    $contentType = apache_request_headers()["Content-Type"];

    if (!(str_contains($contentType, $type->value) || (str_contains($type->value, "/*") && str_contains($contentType, str_split($type->value, "/")[0])) || (str_contains($type->value, "*/") && str_contains($contentType, str_split($type->value, "/")[1])))) {
        status_exit(HTTP_STATES::UNSUPPORTED_MEDIA_TYPE);
    }
}