<?php

namespace oravix\security;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class RoleRestricted {
    public function __construct(array $roles) {

    }
}