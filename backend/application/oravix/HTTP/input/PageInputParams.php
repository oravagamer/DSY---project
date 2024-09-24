<?php

namespace oravix\HTTP\input;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class PageInputParams {
    /**
     * @param ?string[] $allowedColumns
     */
    public function __construct(public string $defaultSortBy, public ?array $allowedColumns = [], public ?bool $ascending = true) {
    }

}