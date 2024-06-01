<?php

namespace oravix\HTTP;

class HttpResponse {
    private HttpStates $status;
    private string|array|null $response;

    /**
     * @param HttpStates $status
     * @param array|string|null $response
     */
    public function __construct(HttpStates $status, array|string|null $response) {
        $this->status = $status;
        $this->response = $response;
    }

    public function getStatus(): HttpStates {
        return $this->status;
    }

    public function getResponse(): array|string|null {
        return $this->response;
    }

}