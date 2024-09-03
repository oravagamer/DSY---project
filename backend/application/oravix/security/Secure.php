<?php

namespace oravix\security;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Secure {
    public function __construct($role = "default" string|int) {

    }
}
