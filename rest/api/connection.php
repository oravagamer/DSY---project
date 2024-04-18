<?php
include_once "./redirect.php";

function getConnection() {
    $servername = '127.0.0.1';
    $username = 'root';
    $password = '';
    $dbname = 'dsy_project_vroj';
    $port = 3306;
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $connection = null;

    try {
        $connection = new mysqli($servername, $username, $password, $dbname, $port);
    } catch (Exception $e) {
        echo $e->getMessage();
        redirect("/");
        exit;
    }

    return $connection;
}