<?php

namespace oravix\HTTP\input;

use Attribute;

#[
    Attribute(Attribute::TARGET_PROPERTY)
]
class JsonValue {
    public function __construct(public string $name, public ?bool $required = false, public ?string $regex = "/(^$|.|\R)+/") {
    }
}