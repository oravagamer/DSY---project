<?php

namespace oravix\HTTP;

class HttpHeader {
    public function __construct(private readonly string $name, private readonly string $value) {
    }

    public function toString(): string {
        return $this->name . ": " . $this->value;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getValue(): string {
        return $this->value;
    }

}