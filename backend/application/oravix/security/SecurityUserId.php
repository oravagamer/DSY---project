<?php

namespace oravix\security;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class SecurityUserId {
    public function __construct() {

    }
}