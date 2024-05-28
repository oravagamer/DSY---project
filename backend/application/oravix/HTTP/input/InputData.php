<?php

namespace oravix\HTTP\input;

use Attribute;
use oravix\HTTP\ContentType;

#[
    Attribute(Attribute::TARGET_PROPERTY)
]
class InputData {
    public function __construct(string $name, ContentType $contentType, bool $required, mixed $default = "") {
    }
}