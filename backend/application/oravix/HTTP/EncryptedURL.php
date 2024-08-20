<?php

namespace oravix\HTTP;

class EncryptedURL {
    public function __construct(private string $path, private array $variables, private readonly string $nonce) {
    }

    public function toString() {
        foreach ($this->variables as $key => $value) {
            $this->variables[$key] = sodium_bin2base64(sodium_crypto_box($value, $this->nonce, $_SESSION["keypair"]), SODIUM_BASE64_VARIANT_ORIGINAL);
        }
        $this->variables["nonce"] = sodium_bin2base64($this->nonce, SODIUM_BASE64_VARIANT_ORIGINAL);
        return $this->path . "?" . http_build_query($this->variables);
    }

}