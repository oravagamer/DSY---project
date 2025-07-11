<?php

namespace oravix\security\rest\api;

use Error;
use Exception;
use oravix\exceptions\HttpException;
use oravix\HTTP\Consumes;
use oravix\HTTP\ContentType;
use oravix\HTTP\Controller;
use oravix\HTTP\EncryptedURL;
use oravix\HTTP\HttpHeader;
use oravix\HTTP\HttpMethod;
use oravix\HTTP\HttpResponse;
use oravix\HTTP\HttpStates;
use oravix\HTTP\input\HeaderInput;
use oravix\HTTP\input\Json;
use oravix\HTTP\input\PathVariable;
use oravix\HTTP\input\PlainText;
use oravix\HTTP\Produces;
use oravix\HTTP\Request;
use oravix\security\JOSE\Security;
use oravix\security\rest\api\data\LoginData;
use oravix\security\rest\api\data\TokensData;
use oravix\security\rest\api\data\RegisterData;
use oravix\security\Secure;
use oravix\security\SecurityUserId;
use PDO;
use PDOException;

#[
    Controller("/security")
]
class SecurityHttpActions {
    private static Security $security;
    private PDO|null $connection;

    public function __construct() {
        self::$security = new Security();
        try {
            $this->connection = new PDO("mysql:host=" . $_ENV["settings"]["JWT_DB_SERVER"] . ";dbname=" . $_ENV["settings"]["JWT_DB_DATABASE_NAME"] . ";port=" . $_ENV["settings"]["JWT_DB_PORT"], $_ENV["settings"]["JWT_DB_USERNAME"], $_ENV["settings"]["JWT_DB_PASSWORD"]);
        } catch (Error|Exception $e) {
            statusExit(HttpStates::INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }

    #[
        Request(
            "/register",
            HttpMethod::POST
        ),
        Consumes(ContentType::APPLICATION_JSON),
        Produces(ContentType::APPLICATION_JSON)
    ]
    function register(
        #[Json] RegisterData                  $registerData,
        #[PathVariable("redirect-url", true)] $redirectUrl,
        #[HeaderInput("win-id")]              $windowId
    ): void {
        try {
            $this
                ->connection
                ->prepare("INSERT INTO users (username, first_name, last_name, email, password) values (:username, :first_name, :last_name, :email, :password)")
                ->execute([
                    "username" => $registerData->username,
                    "password" => password_hash($registerData->password, PASSWORD_DEFAULT),
                    "first_name" => $registerData->firstName,
                    "last_name" => $registerData->lastName,
                    "email" => $registerData->email
                ]);
            self::$security->createRedirectEmailSession("register", json_encode([
                "redirect-url" => $redirectUrl,
                "win-id" => $windowId
            ]), $registerData->email);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                throw new HttpException(HttpStates::CONFLICT, $e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    #[
        Request(
            "/activate",
            HttpMethod::POST
        ),
        Consumes(ContentType::TEXT_PLAIN)
    ]
    function activate(
        #[PlainText] string                   $usernameOrEmail,
        #[PathVariable("redirect-url", true)] $redirectUrl,
        #[HeaderInput("win-id")]              $windowId
    ): void {
        if (str_contains("@", $usernameOrEmail)) {
            $email = $usernameOrEmail;
        } else {
            $statement = $this->connection->prepare("SELECT email FROM users WHERE username = :username");
            $statement->execute([
                "username" => $usernameOrEmail
            ]);
            [
                "email" => $email
            ] = $statement->fetch(PDO::FETCH_NAMED);
        }
        try {
            self::$security->createRedirectEmailSession("activate", json_encode([
                "redirect-url" => $redirectUrl,
                "win-id" => $windowId
            ]), $email);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                throw new HttpException(HttpStates::CONFLICT, $e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    #[
        Request(
            "/login",
            HttpMethod::POST
        ),
        Consumes(ContentType::APPLICATION_JSON),
        Produces(ContentType::TEXT_PLAIN)
    ]
    function login(
        #[Json] LoginData                     $loginData,
        #[PathVariable("redirect-url", true)] $redirectUrl,
        #[HeaderInput("win-id")]              $windowId
    ): HttpResponse {
        if ($email = self::$security->getUserEmailWithVerification($loginData)) {
            self::$security->createRedirectEmailSession("login", json_encode(["redirect-url" => $redirectUrl, "win-id" => $windowId]), $email);
            return new HttpResponse();
        } else {
            return new HttpResponse(status: HttpStates::FORBIDDEN);
        }
    }

    #[
        Request(
            "/change-email",
            HttpMethod::POST
        ),
        Consumes(ContentType::NO_CONTENT),
        Produces(ContentType::TEXT_PLAIN),
        Secure
    ]
    function changeEmail(
        #[SecurityUserId] string              $userId,
        #[PathVariable("email", true, "/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/")] string $email,
        #[PathVariable("redirect-url", true)] $redirectUrl,
        #[HeaderInput("win-id")]              $windowId
    ) {
        if ($oldEmail = self::$security->getUserEmailById($userId)) {
            $sessionId = self::$security->createSession("verify-email", json_encode(["old-email" => $email, "email" => $email, "redirect_url" => $redirectUrl, "win-id" => $windowId]), $userId);
            parse_str($_SERVER["REDIRECT_QUERY_STRING"], $query);
            [
                "path" => $redirectString
            ] = $query;
            mail(
                $oldEmail,
                "Oravix change-email action",
                "<a target='_blank' href='" . (new EncryptedURL($_SERVER["HTTP_X_FORWARDED_PROTO"] . "://" . $_SERVER["HTTP_X_FORWARDED_HOST"] . "/" . str_replace("change-email", "session", $redirectString), [
                    "session" => $sessionId,
                    "action" => "verify-email"
                ], random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES)))->toString() . "'>Verify</a>",
                'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/html; charset=iso-8859-1' . "\r\n" . "From: DoNotReply@abell12.com"
            );
        } else {
            return new HttpResponse(status: HttpStates::FORBIDDEN);
        }
    }

    #[
        Request(
            "/change-password",
            HttpMethod::POST
        ),
        Consumes(ContentType::TEXT_PLAIN),
        Produces(ContentType::TEXT_PLAIN),
        Secure
    ]
    function changePassword(
        #[SecurityUserId] string $userId,
        #[PlainText("/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*\W).{8,16}$/")] string      $password
    ) {
        if ($email = self::$security->getUserEmailById($userId)) {
            self::$security->createRedirectEmailSession("change-password", json_encode(["password" => password_hash($password, PASSWORD_DEFAULT), "redirect-url" => $_ENV["settings"]["JWT_DEFAULT_URL"]]), $email);
        } else {
            return new HttpResponse(status: HttpStates::FORBIDDEN);
        }
    }


    #[
        Request(
            "/refresh-token",
            HttpMethod::POST
        ),
        Consumes(ContentType::APPLICATION_JSON),
        Produces(ContentType::APPLICATION_JSON)
    ]
    function refreshToken(
        #[Json] TokensData $tokensData
    ): HttpResponse {
        return new HttpResponse(
            self::$security->refreshToken($tokensData));
    }

    #[
        Request(
            "/logout",
            HttpMethod::POST
        ),
        Consumes(ContentType::APPLICATION_JSON)
    ]
    function logout(
        #[Json] TokensData $tokensData
    ): void {
        self::$security->logout($tokensData);
    }

    #[
        Request(
            "/session",
            HttpMethod::GET
        ),
        Consumes(ContentType::NO_CONTENT),
        Produces(ContentType::TEXT_HTML)
    ]
    function session(
        #[PathVariable("session", true)] string $session,
        #[PathVariable("action", true)] string  $action
    ): HttpResponse {
        $statement = $this->connection->prepare("SELECT user_id, params FROM sessions WHERE id = :id AND action = :action AND used = FALSE");
        $statement->execute([
            "id" => $session,
            "action" => $action
        ]);
        [
            "user_id" => $userId,
            "params" => $parameters
        ] = $statement->fetch();
        $parameters = json_decode($parameters, true);
        if (is_null($userId)) {
            return new HttpResponse(status: HttpStates::NOT_FOUND);
        }
        $this->connection->prepare("UPDATE sessions SET used = TRUE WHERE id = :id")->execute([
            "id" => $session
        ]);
        switch ($action) {
            case "register":
            case "activate":
            {
                $this
                    ->connection
                    ->prepare("UPDATE users SET active = TRUE WHERE id = :id")
                    ->execute([
                        "id" => $userId
                    ]);
                return new HttpResponse(status: HttpStates::MOVED_PERMANENTLY, headers: [
                    new HttpHeader("Location", (new EncryptedURL($_ENV["settings"]["JWT_REDIRECT_URL"], [
                        "redirect-url" => $parameters["redirect-url"],
                        "win-id" => $parameters["win-id"]
                    ], random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES)))->toString())
                ]);
            }
            case "login":
            {
                [
                    "access" => $accessToken,
                    "refresh" => $refreshToken
                ] = self::$security->login($userId);
                return new HttpResponse(status: HttpStates::MOVED_PERMANENTLY, headers: [
                    new HttpHeader("Location",
                        (new EncryptedURL($_ENV["settings"]["JWT_REDIRECT_URL"], [
                            "access" => $accessToken,
                            "refresh" => $refreshToken,
                            "redirect-url" => $parameters["redirect-url"],
                            "win-id" => $parameters["win-id"]
                        ], random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES)))->toString())
                ]);
            }
            case "change-email":
            {
                $this
                    ->connection
                    ->prepare("UPDATE users SET email = :email WHERE id = :id")
                    ->execute([
                        "email" => $parameters["old-email"],
                        "id" => $userId
                    ]);
                return new HttpResponse(status: HttpStates::MOVED_PERMANENTLY, headers: [
                    new HttpHeader("Location",
                        (new EncryptedURL($_ENV["settings"]["JWT_REDIRECT_URL"], [
                            "redirect-url" => $parameters["redirect-url"],
                            "win-id" => $parameters["win-id"]
                        ], random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES)))->toString())
                ]);
            }
            case "verify-email":
            {
                parse_str($_SERVER["REDIRECT_QUERY_STRING"], $query);
                [
                    "path" => $redirectString
                ] = $query;
                $sessionId = self::$security->createSession("change-email", json_encode($parameters), $userId);
                mail(
                    $parameters["email"],
                    "Oravix change-email action",
                    "<a target='_blank' href='" . (new EncryptedURL($_SERVER["HTTP_X_FORWARDED_PROTO"] . "://" . $_SERVER["HTTP_X_FORWARDED_HOST"] . "/" . str_replace($action, "session", $redirectString), [
                        "session" => $sessionId,
                        "action" => "change-email"
                    ], random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES)))->toString() . "'>Verify</a>",
                    'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/html; charset=iso-8859-1' . "\r\n" . "From: DoNotReply@abell12.com"
                );
                echo "Close tab";
                exit();
            }
            case "change-password":
            {
                $this
                    ->connection
                    ->prepare("UPDATE users SET password = :password WHERE id = :id")
                    ->execute([
                        "password" => $parameters["password"],
                        "id" => $userId
                    ]);
                return new HttpResponse(status: HttpStates::MOVED_PERMANENTLY, headers: [
                    new HttpHeader("Location",
                        (new EncryptedURL($_ENV["settings"]["JWT_REDIRECT_URL"], [
                            "redirect-url" => $parameters["redirect-url"],
                            "win-id" => $parameters["win-id"]
                        ], random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES)))->toString())
                ]);
            }
        }

        return new HttpResponse();
    }

    #[
        Request(
            "/encryption",
            HttpMethod::POST
        ),
        Consumes(ContentType::TEXT_PLAIN),
        Produces(ContentType::TEXT_PLAIN)
    ]
    function encryption(
        #[PlainText] string $publicKey
    ) {
        $newKeypair = sodium_crypto_box_keypair();
        $localSecretKey = sodium_crypto_box_secretkey($newKeypair);
        $keypair = sodium_crypto_box_keypair_from_secretkey_and_publickey($localSecretKey, sodium_base642bin($publicKey, SODIUM_BASE64_VARIANT_ORIGINAL));
        $_SESSION["keypair"] = $keypair;
        return new HttpResponse(sodium_bin2base64(sodium_crypto_box_publickey($newKeypair), SODIUM_BASE64_VARIANT_ORIGINAL), HttpStates::OK);
    }

    #[
        Request(
            "/window-id",
            HttpMethod::GET
        ),
        Consumes(ContentType::NO_CONTENT),
        Produces(ContentType::NO_CONTENT)
    ]
    function getWindowId() {
        $n = 20;
        $id = uniqid(bin2hex(random_bytes($n)));
        return new HttpResponse(status: HttpStates::OK, headers: [
            new HttpHeader("win-id", $id)
        ]);
    }
}