<?php

namespace oravix\HTTP\input;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class PathVariable {
    public function __construct(string $name, ?bool $required = false, mixed $default = "") {
    }
}