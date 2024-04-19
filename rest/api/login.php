<?php
include_once "./conncetion.php";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $jsonData = file_get_contents('php://input');
  $data = json_decode($jsonData, true);
  if ($data !== null) {
    $username = $data["username"];
    $password = $data["password"];

    
  } else {
   http_response_code(400);
  }
}
