<?php

namespace oravix\security\rest\api\role;

use oravix\HTTP\input\JsonValue;

class RoleUpdateData {
    #[JsonValue("name", false)]
    public string $name;
    #[JsonValue("description", false)]
    public string $description;
    #[JsonValue("level", false)]
    public int $level;

}