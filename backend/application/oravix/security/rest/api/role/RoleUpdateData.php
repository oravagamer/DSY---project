<?php

namespace oravix\security\rest\api\role;

use oravix\HTTP\input\JsonValue;

class RoleUpdateData {
    #[JsonValue("name", false, "/.{0,255}/")]
    public string $name;
    #[JsonValue("description", false, "/(.|\R){0,255}/")]
    public string $description;
    #[JsonValue("level", false, "/^(?:1?[0-9]{1,2}|2[0-4][0-9]|25[0-5])$/")]
    public int $level;

}