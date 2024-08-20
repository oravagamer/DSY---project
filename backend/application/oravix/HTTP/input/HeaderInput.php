<?php

namespace oravix\HTTP\input;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class HeaderInput {
    public function __construct(string $name) {
    }

}