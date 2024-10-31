<?php

namespace oravix\security\JOSE;

use OpenSSLAsymmetricKey;

class JWT {
    private Header $JoseHeader;
    private Payload $JwtPayload;
    private string|null $JwtSignature;

    public function __construct(Header $header, Payload $payload, ?string $signature) {
        $this->JoseHeader = $header;
        $this->JwtPayload = $payload;
        $this->JwtSignature = $signature;
    }

    public static function encode(Header $header, Payload $payload, string|OpenSSLAsymmetricKey $key = ""): string {
        $signature = "";
        switch ($header->getAlgorithm()->getAlgFamily()) {
            case AlgorithmFamily::HS:
            {
                $signature = hash_hmac($header->getAlgorithm()->getPhpName(), $header->getVersionBase64() . "." . $payload->getVersionBase64(), $key);
                break;
            }
            case AlgorithmFamily::NONE:
            {
                break;
            }
            default:
            {
                $signature = self::signature($header->getVersionBase64() . "." . $payload->getVersionBase64(), $key, $header->getAlgorithm()->getPhpName());
            }
        }
        return $header->getVersionBase64() . "." . $payload->getVersionBase64() . "." . base64_encode($signature);
    }

    public static function decode(string $token): JWT {
        list($base64Header, $base64Payload, $signature) = explode(".", $token);
        return new JWT((new Header(JWA::$NONE))->loadData($base64Header), (new Payload())->loadData($base64Payload), base64_decode($signature));
    }


    public function isValid($key = null): bool {
        $data = $this->JoseHeader->getVersionBase64() . "." . $this->JwtPayload->getVersionBase64();
        return ($this
                    ->JoseHeader
                    ->getAlgorithm()
                    ->getAlgFamily() == AlgorithmFamily::NONE
                || ($this
                    ->JoseHeader
                    ->getAlgorithm()
                    ->getAlgFamily() == AlgorithmFamily::HS
                    ? password_verify($data, $this->JwtSignature)
                    : self::verifySignature($key, $data, $this
                        ->JwtSignature, $this
                        ->JoseHeader
                        ->getAlgorithm()
                        ->getPhpName())));
    }

    public function isExpired(): bool {
        return ($this->JwtPayload->getIssuedAt() + $this->JwtPayload->getExpirationTime()) <= time();
    }

    public
    function getJoseHeader(): Header {
        return $this->JoseHeader;
    }

    public
    function getJwtPayload(): Payload {
        return $this->JwtPayload;
    }

    public
    function getJwtSignature(): ?string {
        return $this->JwtSignature;
    }

    static function signature(string $plainData, $privateKey, string $algo): false|string {
        $encryptionOk = openssl_sign($plainData, $encryptedData, $privateKey, $algo);
        if ($encryptionOk === false) {
            return false;
        }
        return $encryptedData;
    }

    static function verifySignature($publicKey, string $data, string $signature, string $algo): bool {
        return openssl_verify($data, $signature, $publicKey, "SHA256");
    }

    public function toString(): string {
        return $this->JoseHeader->getVersionBase64() . "." . $this->JwtPayload->getVersionBase64() . "." . $this->JwtSignature;
    }

}