<?php
include_once "./connection.php";
function generate_jwt_tokens($username) {
  try {
    $connection = get_connection();
  } catch (Exception $exception) {
    http_response_code(500);
  }
}
