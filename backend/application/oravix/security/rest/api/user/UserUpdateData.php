<?php

namespace oravix\security\rest\api\user;

use oravix\HTTP\input\JsonValue;

class UserUpdateData {
    #[JsonValue("username", false)]
    public string $username;
    #[JsonValue("first_name", false)]
    public string $firstName;
    #[JsonValue("last_name", false)]
    public string $lastName;
    #[JsonValue("email", false)]
    public string $email;

}