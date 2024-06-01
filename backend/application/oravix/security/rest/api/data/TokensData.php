<?php

namespace oravix\security\rest\api\data;

use oravix\HTTP\input\JsonValue;

class TokensData {
    #[JsonValue("access", true)]
    public string $accessToken;
    #[JsonValue("refresh", true)]
    public string $refreshToken;
}