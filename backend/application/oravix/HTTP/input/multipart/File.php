<?php

namespace oravix\HTTP\input\multipart;

class File {
    public string $name;
    public string $fullPath;
    public string $type;
    public string $tpmName;
    public int $error;
    public int $size;

    /**
     * @param string $name
     * @param string $fullPath
     * @param string $type
     * @param string $tpmName
     * @param int $error
     * @param int $size
     */
    public function __construct(string $name, string $fullPath, string $type, string $tpmName, int $error, int $size) {
        $this->name = $name;
        $this->fullPath = $fullPath;
        $this->type = $type;
        $this->tpmName = $tpmName;
        $this->error = $error;
        $this->size = $size;
    }


}