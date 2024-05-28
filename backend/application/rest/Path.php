<?php

namespace rest;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Path {
    public function __construct(string $name) {
    }

}