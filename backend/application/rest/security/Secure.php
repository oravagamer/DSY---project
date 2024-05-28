<?php

namespace rest\security;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Secure {
    public function __construct() {

    }
}