<?php
include_once "./net_funcs.php";
include_once "./settings.php";

function get_connection(): ?mysqli {
    $settings = get_settings()["db"];
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $connection = null;

    try {
        $connection = new mysqli($settings["server"], $settings["username"], $settings["password"], $settings["db_name"], $settings["port"]);
    } catch (Exception $e) {
        status_exit(500);
    }

    return $connection;
}
