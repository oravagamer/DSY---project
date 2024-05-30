<?php


namespace order;

use oravix\HTTP\ContentType;
use oravix\HTTP\input\multipart\File;
use oravix\HTTP\input\multipart\InputData;

class OrderUploadData {
    #[InputData("name", true)]
    public string $name;
    #[InputData("description", true)]
    public string $description;
    #[InputData("finish_date", true)]
    public string $finishDate;
    #[InputData("created_for", false)]
    public string|null $createdFor;
    /**
     * @var File[]|null
     */
    #[InputData("images", true, ContentType::ALL_IMAGES)]
    public array|null $images;

}