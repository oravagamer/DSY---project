<?php

namespace oravix\HTTP;
enum ContentType: string {
    case APPLICATION_JSON = "application/json";
    case ALL_IMAGES = "image/*";
    case MULTIPART_FORM_DATA = "multipart/form-data";
    case APPLICATION_OCTET_STREAM = "application/octet-stream";
    case TEXT_PLAIN = "text/plain";
    case NO_CONTENT = "";
}