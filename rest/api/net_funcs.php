<?php
function redirect(string $url): void {
    header("Location: " . $url);
}

function status_exit($net_status) {
    http_response_code($net_status);
    exit(0);
}