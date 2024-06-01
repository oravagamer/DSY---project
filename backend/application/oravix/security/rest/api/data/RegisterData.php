<?php

namespace oravix\security\rest\api\data;

use oravix\HTTP\input\JsonValue;

class RegisterData {
    #[JsonValue("username", true)]
    public string $username;
    #[JsonValue("password", true)]
    public string $password;
    #[JsonValue("first_name", true)]
    public string $firstName;
    #[JsonValue("last_name", true)]
    public string $lastName;
    #[JsonValue("email", true)]
    public string $email;
}