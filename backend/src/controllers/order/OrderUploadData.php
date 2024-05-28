<?php


namespace order;

use oravix\HTTP\ContentType;
use oravix\HTTP\input\InputData;

class OrderUploadData {
    #[InputData("name", ContentType::TEXT_PLAIN, true)]
    public string $name;
    #[InputData("description", ContentType::TEXT_PLAIN, true)]
    public string $description;
    #[InputData("finish_date", ContentType::TEXT_PLAIN, true)]
    public string $finishDate;
    #[InputData("created_for", ContentType::TEXT_PLAIN, false)]
    public string $createdFor;

}