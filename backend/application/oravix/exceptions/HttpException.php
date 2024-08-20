<?php

namespace oravix\exceptions;

use Exception;
use oravix\HTTP\HttpStates;
use Throwable;

class HttpException extends Exception {
    public function __construct(private HttpStates $state, ?string $message = null, ?Throwable $previous = null) {
        parent::__construct($message === null ? $state->name : $message, $state->value, $previous);
    }

    public function getState(): HttpStates {
        return $this->state;
    }
}