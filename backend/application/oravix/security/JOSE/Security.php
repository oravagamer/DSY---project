<?php

namespace oravix\security\JOSE;

use Error;
use Exception;
use oravix\HTTP\HttpStates;
use oravix\security\rest\api\data\LoginData;
use oravix\security\rest\api\data\TokensData;
use oravix\security\rest\api\data\RegisterData;
use PDO;
use PDOException;
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

    public function register(RegisterData $registerData): void {
        $statement = $this->connection->prepare("INSERT INTO users (username, first_name, last_name, email, password) values (:username, :first_name, :last_name, :email, :password)");
        try {
            $statement->execute([
                "username" => $registerData->username,
                "password" => password_hash($registerData->password, PASSWORD_DEFAULT),
                "first_name" => $registerData->firstName,
                "last_name" => $registerData->lastName,
                "email" => $registerData->email
            ]);
        } catch (PDOException $e) {
            statusExit(HttpStates::INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }

    public function login(LoginData $loginData): array {
        $header = new Header($this->algorithm);
        $statement = $this->connection->prepare("SELECT id, password FROM users WHERE username = :username OR email = :username");
        $statement->execute([
            "username" => $loginData->username
        ]);
        $data = $statement->fetch();
        if (!password_verify($loginData->password, $data["password"]) || $statement->rowCount() !== 1) {
            statusExit(HttpStates::FORBIDDEN);
        }
        $accessPayload = (new Payload())
            ->setExpirationTime($_ENV["settings"]["JWT_ACCESS_EXPIRATION"])
            ->setIssuer($_SERVER["HTTP_HOST"])
            ->setIssuedAt(time())
            ->setSubject($data["id"]);
        $refreshPayload = (new Payload())
            ->setExpirationTime($_ENV["settings"]["JWT_REFRESH_EXPIRATION"])
            ->setIssuer($accessPayload->getIssuer())
            ->setIssuedAt($accessPayload->getIssuedAt())
            ->setSubject($accessPayload->getSubject());
        $accessToken = new JWT($header, $accessPayload, new JWS($header, $accessPayload, $this->connection, false));
        $refreshToken = new JWT($header, $refreshPayload, new JWS($header, $refreshPayload, $this->connection, true));
        return [
            "access" => $accessToken,
            "refresh" => $refreshToken
        ];
    }

    public function logout(TokensData $tokensData): void {
        $accessToken = JWT::autoLoad($tokensData->accessToken);
        $refreshToken = JWT::autoLoad($tokensData->refreshToken);
        $statement = $this->connection->prepare("UPDATE tokens SET status = FALSE WHERE id = :access_id || id = :refresh_id");
        $statement->execute([
            "access_id" => $accessToken->getJwtPayload()->getJwtId(),
            "refresh_id" => $refreshToken->getJwtPayload()->getJwtId()
        ]);

    }

    public function refreshToken(TokensData $tokensData): array {
        $oldAccessToken = JWT::autoLoad($tokensData->accessToken);
        $oldRefreshToken = JWT::autoLoad($tokensData->refreshToken);
        $oldAccessToken->typeAndValidityCheck(false, $this->connection);
        $oldRefreshToken->typeAndValidityCheck(true, $this->connection);

        if ($oldRefreshToken->isExpired()) {
            statusExit(HttpStates::FORBIDDEN);
        } else {
            $this->logout($tokensData);

            $accessToken = new JWT($oldAccessToken->getJoseHeader(), $oldAccessToken->getJwtPayload()->setIssuedAt(time()), new JWS($oldAccessToken->getJoseHeader(), $oldAccessToken->getJwtPayload()->setIssuedAt(time()), $this->connection, false));
            $refreshToken = new JWT($oldRefreshToken->getJoseHeader(), $oldRefreshToken->getJwtPayload()->setIssuedAt(time()), new JWS($oldRefreshToken->getJoseHeader(), $oldRefreshToken->getJwtPayload()->setIssuedAt(time()), $this->connection, true));
            return [
                "access" => $accessToken,
                "refresh" => $refreshToken
            ];
        }

    }

    public function secure(): string {
        try {
            $authorization = isset(apache_request_headers()["Authorization"]) ? apache_request_headers()["Authorization"] : isset(apache_request_headers()["authorization"]);
            if (!isset($authorization)) {
                statusExit(HttpStates::FORBIDDEN);
            } else {
                list(, $rawToken) = explode("Bearer ", $authorization);
                $token = JWT::autoLoad($rawToken);
                $token->typeAndValidityCheck(false, $this->connection);
                if ($token->isExpired()) {
                    statusExit(HttpStates::FORBIDDEN);
                }

                return $token->getJwtPayload()->getSubject();
            }
        } catch (Error $e) {
            statusExit(HttpStates::INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }


}