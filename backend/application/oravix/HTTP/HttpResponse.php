<?php

namespace oravix\HTTP;

class HttpResponse {
    private HttpStates $status;
    private string|array|null $response;

    /**
     * @param array|string|null $response
     * @param HttpStates|null $status
     * @param HttpHeader[]|null $headers
     */
    public function __construct(array|string|null $response, ?HttpStates $status = HttpStates::OK, ?array $headers = null) {
        $this->status = $status;
        $this->response = $response;
        if (!is_null($headers)) {
            foreach ($headers as $header) {
                header($header->toString());
            }
        }
    }

    public function getStatus(): HttpStates {
        return $this->status;
    }

    public function getResponse(): array|string|null {
        return $this->response;
    }

}