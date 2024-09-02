<?php

namespace oravix\security\JOSE;

use Error;
use Exception;
use oravix\exceptions\HttpException;
use oravix\HTTP\EncryptedURL;
use oravix\HTTP\HttpStates;
use oravix\security\rest\api\data\LoginData;
use oravix\security\rest\api\data\TokensData;
use PDO;
use ReflectionClass;

class Security {
    private Algorithm|null $algorithm = null;
    private PDO|null $connection;

    public function __construct() {

        $JwaRef = new ReflectionClass(new JWA());
        foreach ($JwaRef->getProperties() as $property) {
            $alg = $property->getValue();
            if ($alg->getJwtName() === $_ENV["settings"]["JWT_ALGORITHM"]) {
                $this->algorithm = $alg;
                break;
            }
        }

        if (!is_null($this->algorithm)) {
            try {
                $this->connection = new PDO("mysql:host=" . $_ENV["settings"]["JWT_DB_SERVER"] . ";dbname=" . $_ENV["settings"]["JWT_DB_DATABASE_NAME"] . ";port=" . $_ENV["settings"]["JWT_DB_PORT"], $_ENV["settings"]["JWT_DB_USERNAME"], $_ENV["settings"]["JWT_DB_PASSWORD"]);
            } catch (Error|Exception $e) {
                statusExit(HttpStates::INTERNAL_SERVER_ERROR, $e->getMessage());
            }

        } else {
            statusExit(HttpStates::INTERNAL_SERVER_ERROR, "JWT algorithm not found.");
        }
    }

    public function login(string $userId): array {
        $header = new Header($this->algorithm);
        ["id" => $refId] = $this->connection->query("SELECT  UUID() as id")->fetch();
        ["id" => $accId] = $this->connection->query("SELECT  UUID() as id")->fetch();
        $accPayload = (new Payload())
            ->setIssuer($_SERVER["HTTP_HOST"])
            ->setSubject($userId)
            ->setAudience($_SERVER["HTTP_X_FORWARDED_HOST"])
            ->setExpirationTime($_ENV["settings"]["JWT_ACCESS_EXPIRATION"])
            ->setNotBefore(time() + $_ENV["settings"]["JWT_ACCESS_NOT_BEFORE"])
            ->setIssuedAt(time())
            ->setJwtId($accId);
        $refPayload = (new Payload())
            ->setIssuer($_SERVER["HTTP_HOST"])
            ->setSubject($userId)
            ->setAudience($_SERVER["HTTP_X_FORWARDED_HOST"])
            ->setExpirationTime($_ENV["settings"]["JWT_REFRESH_EXPIRATION"])
            ->setNotBefore(time() + $_ENV["settings"]["JWT_REFRESH_NOT_BEFORE"])
            ->setIssuedAt(time())
            ->setJwtId($refId);
        switch ($this->algorithm->getAlgFamily()) {
            case AlgorithmFamily::HS:
            {
                $accKey = base64_encode(random_bytes(256));
                $refKey = base64_encode(random_bytes(256));
                $this
                    ->connection
                    ->prepare("INSERT INTO acc_tokens(id, user_id, secret_key) VALUES (:id, :user_id, :key)")
                    ->execute([
                        "id" => $accId,
                        "user_id" => $userId,
                        "key" => $accKey
                    ]);
                $this
                    ->connection
                    ->prepare("INSERT INTO ref_tokens(id, user_id, secret_key) VALUES (:id, :user_id, :key)")
                    ->execute([
                        "id" => $refId,
                        "user_id" => $userId,
                        "key" => $refKey
                    ]);
                $accessToken = JWT::encode($header, $accPayload, $accKey);
                $refreshToken = JWT::encode($header, $refPayload, $refKey);
                break;
            }
            case AlgorithmFamily::NONE:
            {
                $accessToken = JWT::encode(new Header(JWA::$NONE), $accPayload);
                $refreshToken = JWT::encode(new Header(JWA::$NONE), $refPayload);
                break;
            }
            default:
            {
                $accPvRawKey = openssl_pkey_new();
                $refPvRawKey = openssl_pkey_new();
                openssl_pkey_export($accPvRawKey, $accPvKey);
                openssl_pkey_export($refPvRawKey, $refPvKey);
                $this
                    ->connection
                    ->prepare("INSERT INTO acc_tokens(id, user_id, secret_key) VALUES (:id, :user_id, :key)")
                    ->execute([
                        "id" => $accId,
                        "user_id" => $userId,
                        "key" => $accPvKey
                    ]);
                $this
                    ->connection
                    ->prepare("INSERT INTO ref_tokens(id, user_id, secret_key) VALUES (:id, :user_id, :key)")
                    ->execute([
                        "id" => $refId,
                        "user_id" => $userId,
                        "key" => $refPvKey
                    ]);
                $accessToken = JWT::encode($header, $accPayload, $accPvRawKey);
                $refreshToken = JWT::encode($header, $refPayload, $refPvRawKey);
                break;
            }
        }
        return [
            "access" => $accessToken,
            "refresh" => $refreshToken
        ];
    }

    public function createSession(string $action, string $parameters, string $userId): string {
        [
            "id" => $sessionId
        ] = $this->connection->query("SELECT UUID() as id")->fetch();
        $this->connection
            ->prepare("INSERT INTO sessions (id, action, params, user_id) VALUES (:session_id, :action, :parameters, :user_id)")
            ->execute([
                "session_id" => $sessionId,
                "parameters" => $parameters,
                "action" => $action,
                "user_id" => $userId
            ]);
        return $sessionId;
    }

    public function createRedirectEmailSession(string $action, string $parameters, string $to, ?string $userId = null): void {
        if (is_null($userId)) {
            $statement = $this->connection->prepare("SELECT id from users WHERE email = :email");
            $statement->execute([
                "email" => $to
            ]);
            [
                "id" => $userId
            ] = $statement->fetch();
        }
        $sessionId = $this->createSession($action, $parameters, $userId);
        parse_str($_SERVER["REDIRECT_QUERY_STRING"], $query);
        [
            "path" => $redirectString
        ] = $query;
        mail(
            $to,
            "Oravix login verification",
            "<a target='_blank' href='" . (new EncryptedURL($_SERVER["HTTP_X_FORWARDED_PROTO"] . "://" . $_SERVER["HTTP_X_FORWARDED_HOST"] . "/" . str_replace($action, "session", $redirectString), [
                "session" => $sessionId,
                "action" => $action
            ], random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES)))->toString() . "'>Verify</a>",
            'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/html; charset=iso-8859-1' . "\r\n" . "From: DoNotReply@abell12.com"
        );
    }

    public function getUserEmailWithVerification(LoginData $loginData): false|string {
        $statement = $this->connection->prepare("SELECT id, password, email FROM users WHERE username = :username OR email = :username");
        $statement->execute([
            "username" => $loginData->username
        ]);
        $data = $statement->fetch();
        return (password_verify($loginData->password, $data["password"]) || $statement->rowCount() === 1) ? $data["email"] : false;
    }

    public function getUserEmailById(string $userId): false|string {
        $statement = $this->connection->prepare("SELECT email FROM users WHERE id = :id");
        $statement->execute([
            "id" => $userId
        ]);
        [
            "email" => $email
        ] = $statement->fetch();
        return $email;
    }

    public function logout(TokensData $tokensData): void {
        $accessToken = JWT::decode($tokensData->accessToken);
        $refreshToken = JWT::decode($tokensData->refreshToken);
        $this
            ->connection
            ->prepare("UPDATE acc_tokens SET is_terminated = TRUE WHERE id = :id")
            ->execute([
                "id" => $accessToken->getJwtPayload()->getJwtId()
            ]);
        $this
            ->connection
            ->prepare("UPDATE ref_tokens SET is_terminated = TRUE WHERE id = :id")
            ->execute([
                "id" => $refreshToken->getJwtPayload()->getJwtId()
            ]);

    }

    /**
     * @param TokensData $tokensData
     */
    public function refreshToken(TokensData $tokensData): array {
        $oldAccessToken = JWT::decode($tokensData->accessToken);
        $oldRefreshToken = JWT::decode($tokensData->refreshToken);
        $this->verifyAccessToken($oldAccessToken);
        $this->verifyRefreshToken($oldRefreshToken);

        if ($oldRefreshToken->isExpired()) {
            throw new HttpException(HttpStates::FORBIDDEN);
        } else {
            $this->logout($tokensData);
            [
                "access" => $accessToken,
                "refresh" => $refreshToken
            ] = $this->login($oldRefreshToken->getJwtPayload()->getSubject());

            return [
                "access" => $accessToken,
                "refresh" => $refreshToken
            ];
        }

    }

    public function secure(): string {
        $authorization = apache_request_headers()["authorization"];
        if (!isset($authorization)) {
            throw new HttpException(HttpStates::FORBIDDEN);
        } else {
            list(, $rawToken) = explode("Bearer ", $authorization);
            $token = JWT::decode($rawToken);
            $this->verifyAccessToken($token);
            return $token->getJwtPayload()->getSubject();
        }
    }

    public function verifyAccessToken(JWT $token): true {
        if ($token->getJoseHeader()->getAlgorithm() === JWA::$NONE) {
            if (!$token->isValid()) {
                throw new HttpException(HttpStates::FORBIDDEN);
            }
        } else {
            $statement = $this
                ->connection
                ->prepare("SELECT secret_key FROM acc_tokens WHERE id = :id AND user_id = :user_id AND is_terminated = FALSE");
            $statement->execute([
                "id" => $token->getJwtPayload()->getJwtId(),
                "user_id" => $token->getJwtPayload()->getSubject()
            ]);
            [
                "secret_key" => $key
            ] = $statement->fetch();

            if ($token->getJoseHeader()->getAlgorithm() != AlgorithmFamily::HS) {
                $pvKey = openssl_pkey_get_private($key);
                $resCsr = openssl_csr_new(array(), $pvKey);
                $resCert = openssl_csr_sign($resCsr, null, $pvKey, 1);
                openssl_x509_export($resCert, $strCert);
                $key = openssl_csr_get_public_key($resCsr);

            }

            if (!$token->isValid($key)) {
                throw new HttpException(HttpStates::FORBIDDEN);
            }
        }

        return true;
    }

    public function verifyRefreshToken(JWT $token): true {
        if ($token->getJoseHeader()->getAlgorithm() === JWA::$NONE) {
            if (!$token->isValid()) {
                throw new HttpException(HttpStates::FORBIDDEN);
            }
        } else {

            $statement = $this
                ->connection
                ->prepare("SELECT secret_key FROM ref_tokens WHERE id = :id AND user_id = :user_id AND is_terminated = FALSE");
            $statement->execute([
                "id" => $token->getJwtPayload()->getJwtId(),
                "user_id" => $token->getJwtPayload()->getSubject()
            ]);
            [
                "secret_key" => $key
            ] = $statement->fetch();

            if ($token->getJoseHeader()->getAlgorithm() != AlgorithmFamily::HS) {
                $res_csr = openssl_csr_new(array(), $key);
                $res_cert = openssl_csr_sign($res_csr, null, $key, 1);
                openssl_x509_export($res_cert, $str_cert);
                $key = openssl_pkey_get_public($str_cert);
            }

            if (!$token->isValid($key)) {
                throw new HttpException(HttpStates::FORBIDDEN);
            }
        }

        return true;
    }


}