<?php

function redirect(string $url): void {
    header("Location: " . $url);
}

function status_exit($net_status): void {
    http_response_code($net_status);
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

function return_as_json($data) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    status_exit(200);
}