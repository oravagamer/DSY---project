<?php

namespace oravix\HTTP;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Produces {
    public function __construct(?ContentType $contentType = ContentType::NO_CONTENT) {
        header("Content-Type: " . $contentType->value);
    }
}