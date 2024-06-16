<?php

namespace oravix\security\JOSE;

use Exception;
use oravix\HTTP\HttpStates;
use PDO;

/**
 * Defines signed content using JSON-based data structures.
 * The signing/verification is performed with the algorithms defined in @type JWA
 */
class JWS {
    private string $signature = "";

    public function __construct(Header $header, Payload $payload, ?PDO $connection = null, ?bool $type = null) {
        if ($header->getAlgorithm() === JWA::$NONE) {
            return;
        } else {
            switch ($header->getAlgorithm()->getAlgFamily()) {
                case AlgorithmFamily::HS:
                {
                    try {
                        $shaKey = base64_encode(random_bytes(256));
                        $statement = $connection->prepare("CALL generate_token(:user_id, :sha_key, :type)");
                        $statement->execute([
                            "user_id" => $payload->getSubject(),
                            "sha_key" => $shaKey,
                            "type" => $type
                        ]);
                        $data = $statement->fetch();
                        $payload->setJwtId($data["id"]);

                        $this->signature = hash_hmac($header->getAlgorithm()->getPhpName(), $header->getVersionBase64() . "." . $payload->getVersionBase64(), $shaKey);

                    } catch (Exception $e) {
                        statusExit(HttpStates::INTERNAL_SERVER_ERROR, $e->getMessage());
                    }
                    break;
                }
            }
        }
    }

    public function getSignature(): string {
        return $this->signature;
    }

    public function setSignature(string $signature): JWS {
        $this->signature = $signature;
        return $this;
    }

}