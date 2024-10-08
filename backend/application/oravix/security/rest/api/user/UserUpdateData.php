<?php

namespace oravix\security\rest\api\user;

use oravix\HTTP\input\JsonValue;

class UserUpdateData {
    #[JsonValue("username", false, "/.{3,255}/")]
    public string $username;
    #[JsonValue("first_name", false, "/([a-zA-Z]+){3,255}/")]
    public string $firstName;
    #[JsonValue("last_name", false, "/([a-zA-Z]+){3,255}/")]
    public string $lastName;

}