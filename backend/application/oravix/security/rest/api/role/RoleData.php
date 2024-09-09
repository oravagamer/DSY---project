<?php

namespace oravix\security\rest\api\role;

use oravix\HTTP\input\JsonValue;

class RoleData {
    #[JsonValue("name", true)]
    public string $name;
    #[JsonValue("description", false)]
    public string|null $description;
    #[JsonValue("level", true)]
    public int $level;

}