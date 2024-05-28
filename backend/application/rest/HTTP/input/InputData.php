<?php

namespace rest\HTTP\input;

use Attribute;
use rest\HTTP\ContentType;

#[
    Attribute(Attribute::TARGET_PROPERTY)
]
class InputData {
    public function __construct(string $name, ContentType $contentType, bool $required, mixed $default = "") {
    }
}