<?php

namespace oravix\security\JOSE;

readonly class Algorithm {
    public function __construct(
        private string $jwtName,
        private string $phpName,
        private AlgorithmFamily $algFamily
    ) {
    }

    public function getAlgFamily(): AlgorithmFamily {
        return $this->algFamily;
    }

    public function getJwtName(): string {
        return $this->jwtName;
    }

    public function getPhpName(): string {
        return $this->phpName;
    }
}