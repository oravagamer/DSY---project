<?php

namespace oravix\security;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class SecurityData {
    public function __construct() {

    }
}