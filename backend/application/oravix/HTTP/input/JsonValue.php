<?php

namespace oravix\HTTP\input;

use Attribute;

#[
    Attribute(Attribute::TARGET_PROPERTY)
]
class JsonValue {
    public function __construct(string $name, bool $required, mixed $default = "") {
    }
}