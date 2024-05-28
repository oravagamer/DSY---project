<?php

namespace rest\HTTP;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Method {
    public function __construct(HttpMethod $name) {
        if (!($name->value === $_SERVER["REQUEST_METHOD"])) {
            status_exit(HttpStates::NOT_FOUND);
        }
    }
}