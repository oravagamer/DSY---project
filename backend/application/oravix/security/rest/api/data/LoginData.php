<?php

namespace oravix\security\rest\api\data;

use oravix\HTTP\input\JsonValue;

class LoginData {
    #[JsonValue("username", true)]
    public string $username;
    #[JsonValue("password", true)]
    public string $password;
}