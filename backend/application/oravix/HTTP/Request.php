<?php

namespace oravix\HTTP;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Request {
    public function __construct(string $path, HttpMethod $method) {
    }

}