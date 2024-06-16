<?php

namespace order;

use oravix\HTTP\input\JsonValue;

class OrderPutJsonData {
    #[JsonValue("name", false)]
    public string|null $name;
    #[JsonValue("description", false)]
    public string|null $description;
    #[JsonValue("finish_date", false)]
    public string|null $finishDate;
    #[JsonValue("created_for", false)]
    public string|null $createdFor;
    #[JsonValue("status", false)]
    public int|null $status;
}