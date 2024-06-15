<?php

namespace oravix\security\JOSE;

class JWA {
    public static Algorithm $NONE;
    public static Algorithm $HS256;
    public static Algorithm $HS384;
    public static Algorithm $HS512;

    public function __construct() {
        self::$NONE = new Algorithm("none", "none", AlgorithmFamily::NONE);
        self::$HS256 = new Algorithm("HS256", "sha256", AlgorithmFamily::HS);
        self::$HS384 = new Algorithm("HS384", "sha384", AlgorithmFamily::HS);
        self::$HS512 = new Algorithm("HS512", "sha512", AlgorithmFamily::HS);
    }

}
