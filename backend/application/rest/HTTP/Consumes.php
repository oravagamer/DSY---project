<?php

namespace rest\HTTP;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Consumes {
    public function __construct(?ContentType $contentType = ContentType::NO_CONTENT) {
        $providedContentType = isset(apache_request_headers()["Content-Type"]) ? explode(";", apache_request_headers()["Content-Type"], 2)[0] : (isset(apache_request_headers()["content-type"]) ? explode(";", apache_request_headers()["content-type"], 2)[0] : "");

        if (!($providedContentType === $contentType->value
            || (str_contains($contentType->value, "/*")
                && explode("/", $providedContentType)[0] === explode("/", $contentType->value)[0])
            || (str_contains($contentType->value, "*/")
                && explode("/", $providedContentType)[1] === explode("/", $contentType->value)[1]))) {
            status_exit(HTTPStates::UNSUPPORTED_MEDIA_TYPE);
        }
    }
}