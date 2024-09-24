<?php

namespace oravix\HTTP\input;

class PageInput {
    public function __construct(public string  $sortBy,
                                public int     $page,
                                public int     $rowsPerPage,
                                public bool    $ascending,
                                public string|null $searchedColumnName,
                                public mixed $searchedValue
    ) {
    }
}