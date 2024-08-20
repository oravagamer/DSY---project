<?php

namespace oravix\HTTP\input;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class PlainText {
    public function __construct() {
    }
}