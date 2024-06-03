<?php

namespace oravix\HTTP\input;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class FileUpload {
    /**
     * @return string content of whole file
     */
    public function __construct() {
    }

}