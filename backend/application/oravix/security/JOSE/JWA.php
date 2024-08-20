<?php

namespace oravix\security\JOSE;

class JWA {
    public static Algorithm $NONE;
    public static Algorithm $HS256;
    public static Algorithm $HS384;
    public static Algorithm $HS512;
    public static Algorithm $RS256;
    public static Algorithm $RS384;
    public static Algorithm $RS512;

    public static Algorithm $ES256;
    public static Algorithm $ES384;
    public static Algorithm $ES512;

    public static Algorithm $PS256;
    public static Algorithm $PS384;
    public static Algorithm $PS512;


    public function __construct() {
        self::$NONE = new Algorithm("none", "none", AlgorithmFamily::NONE);
        self::$HS256 = new Algorithm("HS256", "sha256", AlgorithmFamily::HS);
        self::$HS384 = new Algorithm("HS384", "sha384", AlgorithmFamily::HS);
        self::$HS512 = new Algorithm("HS512", "sha512", AlgorithmFamily::HS);
        self::$RS256 = new Algorithm("RS256", "SHA256", AlgorithmFamily::RS);
        self::$RS384 = new Algorithm("RS384", "SHA384", AlgorithmFamily::RS);
        self::$RS512 = new Algorithm("RS512", "SHA512", AlgorithmFamily::RS);
        self::$ES256 = new Algorithm("ES256", "SHA256", AlgorithmFamily::ES);
        self::$ES384 = new Algorithm("ES384", "SHA384", AlgorithmFamily::ES);
        self::$ES512 = new Algorithm("ES512", "SHA512", AlgorithmFamily::ES);
        self::$PS256 = new Algorithm("PS256", "SHA256", AlgorithmFamily::PS);
        self::$PS384 = new Algorithm("PS384", "SHA384", AlgorithmFamily::PS);
        self::$PS512 = new Algorithm("PS512", "SHA512", AlgorithmFamily::PS);
    }

}
