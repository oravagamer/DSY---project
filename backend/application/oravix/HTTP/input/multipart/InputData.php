<?php

namespace oravix\HTTP\input\multipart;

use Attribute;
use oravix\HTTP\ContentType;

#[
    Attribute(Attribute::TARGET_PROPERTY)
]
class InputData {
    public function __construct(string $name, bool $required, ?ContentType $contentType = null) {
    }
}