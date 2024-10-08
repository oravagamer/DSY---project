<?php

namespace oravix\security\rest\api\data;

use oravix\HTTP\input\JsonValue;

class RegisterData {
    #[JsonValue("username", true, "/.{3,255}/")]
    public string $username;
    #[JsonValue("password", true, "/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*\W).{8,16}$/")]
    public string $password;
    #[JsonValue("first_name", true, "/([a-zA-Z]+){3,255}/")]
    public string $firstName;
    #[JsonValue("last_name", true, "/([a-zA-Z]+){3,255}/")]
    public string $lastName;
    #[JsonValue("email", true, "/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/")]
    public string $email;
}