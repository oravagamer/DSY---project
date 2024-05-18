<?php
include_once "./HTTP_STATES.php";

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
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    status_exit(HTTP_STATES::OK, "");
}

function cancelWarns(): void {
    error_reporting(E_ERROR | E_PARSE);
}