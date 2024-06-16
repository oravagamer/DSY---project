<?php

namespace oravix\HTTP;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Controller {
    public function __construct(string $path) {
    }
}