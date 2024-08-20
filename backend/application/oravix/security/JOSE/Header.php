<?php

namespace oravix\security\JOSE;

use ReflectionClass;

class Header {
    private Algorithm $algorithm;

    private const Type = "JWT";
    //private string|null $ContentType = null;

    public function getVersionBase64(): string {
        return base64_encode(json_encode([
            "alg" => $this->algorithm->getJwtName(),
            "typ" => self::Type
            //,"cty" => $this->ContentType
        ]));
    }

    /**
     * @param Algorithm $algorithm
     */
    public function __construct(Algorithm $algorithm) {
        $this->algorithm = $algorithm;
    }

    public function loadData(string $base64JsonData): Header {
        $data = json_decode(base64_decode($base64JsonData));
        //$this->ContentType = $data->cty;
        foreach ((new ReflectionClass(new JWA()))->getProperties() as $property) {
            if ($property->getValue()->getJwtName() === $data->alg) {
                $this->algorithm = $property->getValue();
            }
        }

        return $this;
    }

    public function getAlgorithm(): Algorithm {
        return $this->algorithm;
    }


}