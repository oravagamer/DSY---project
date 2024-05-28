<?php

namespace oravix\security\rest\api\data;

use oravix\HTTP\input\JsonValue;

class RefreshTokenData {
    #[JsonValue("access", true)]
    public string $accessToken;
    #[JsonValue("refresh", true)]
    public string $refreshToken;
}