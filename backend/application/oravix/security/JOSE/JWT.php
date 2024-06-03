<?php

namespace oravix\security\JOSE;

use oravix\HTTP\HttpStates;
use PDO;

/**
 * Defines subject claims using JSON-based data structures.
 * The claims can be optionally protected via
 * @type JWS
 */
class JWT {
    private Header $JoseHeader;
    private Payload $JwtPayload;
    private JWS|null $JwtSignature;

    public function __construct(Header $header, Payload $payload, ?JWS $JWS = null) {
        $this->JoseHeader = $header;
        $this->JwtPayload = $payload;
        $this->JwtSignature = $JWS;
    }

    public function typeAndValidityCheck(bool $type, PDO $connection): void {
        switch ($this->JoseHeader->getAlgorithm()->getAlgFamily()) {
            case AlgorithmFamily::HS:
            {
                $statement = $connection->prepare("SELECT sha_key, type FROM tokens WHERE id = :token_id && user_id = :user_id && status = TRUE");
                $statement->execute([
                    "user_id" => $this->JwtPayload->getSubject(),
                    "token_id" => $this->JwtPayload->getJwtId()
                ]);
                $data = $statement->fetch();
                if ($data["type"] xor $type) {
                    statusExit(HttpStates::FORBIDDEN);
                }
                if ($this->JwtSignature->getSignature() !== hash_hmac($this->JoseHeader->getAlgorithm()->getPhpName(), $this->JoseHeader->getVersionBase64() . "." . $this->JwtPayload->getVersionBase64(), $data["sha_key"])) {
                    statusExit(HttpStates::FORBIDDEN);
                }

                break;
            }
            default:
            {
                statusExit(HttpStates::FORBIDDEN);
                break;
            }
        }
        return;
    }

    static function autoLoad(string $token): JWT {
        list($base64Header, $base64Payload, $signature) = explode(".", $token);
        return new JWT((new Header(JWA::$NONE))->loadData($base64Header), (new Payload())->loadData($base64Payload), (new JWS(new Header(JWA::$NONE), new Payload()))->setSignature($signature));

    }

    public function isExpired(): bool {
        return ($this->JwtPayload->getIssuedAt() + $this->JwtPayload->getExpirationTime()) <= time();
    }

    public
    function getJWTString(): string {
        return $this->JoseHeader->getVersionBase64() . "." . $this->JwtPayload->getVersionBase64() . "." . $this->JwtSignature->getSignature();
    }

    public function getJoseHeader(): Header {
        return $this->JoseHeader;
    }

    public function getJwtPayload(): Payload {
        return $this->JwtPayload;
    }

    public function getJwtSignature(): ?JWS {
        return $this->JwtSignature;
    }
}