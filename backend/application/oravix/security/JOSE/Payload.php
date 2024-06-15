<?php

namespace oravix\security\JOSE;

class Payload {
    private ?string $issuer = null;
    private ?string $subject = null;
    private ?string $audience = null;
    private ?int $expirationTime = null;
    private ?int $notBefore = null;
    private ?int $issuedAt = null;
    private ?string $JwtId = null;


    public function getVersionBase64(): string {
        return base64_encode(json_encode([
            "iss" => $this->issuer,
            "sub" => $this->subject,
            "aud" => $this->audience,
            "exp" => $this->expirationTime,
            "nbf" => $this->notBefore,
            "iat" => $this->issuedAt,
            "jti" => $this->JwtId,

        ]));
    }

    public function loadData(string $base64JsonData): Payload {
        $data = json_decode(base64_decode($base64JsonData), true);
        [
            "iss" => $this->issuer,
            "sub" => $this->subject,
            "aud" => $this->audience,
            "exp" => $this->expirationTime,
            "nbf" => $this->notBefore,
            "iat" => $this->issuedAt,
            "jti" => $this->JwtId] = $data;
        return $this;
    }

    public function getIssuer(): string {
        return $this->issuer;
    }

    public function setIssuer(string $issuer): Payload {
        $this->issuer = $issuer;
        return $this;
    }

    public function getSubject(): string {
        return $this->subject;
    }

    public function setSubject(string $subject): Payload {
        $this->subject = $subject;
        return $this;
    }

    public function getAudience(): string {
        return $this->audience;
    }

    public function setAudience(string $audience): Payload {
        $this->audience = $audience;
        return $this;
    }

    public function getExpirationTime(): int {
        return $this->expirationTime;
    }

    public function setExpirationTime(int $expirationTime): Payload {
        $this->expirationTime = $expirationTime;
        return $this;
    }

    public function getNotBefore(): int {
        return $this->notBefore;
    }

    public function setNotBefore(int $notBefore): Payload {
        $this->notBefore = $notBefore;
        return $this;
    }

    public function getIssuedAt(): int {
        return $this->issuedAt;
    }

    public function setIssuedAt(int $issuedAt): Payload {
        $this->issuedAt = $issuedAt;
        return $this;
    }

    public function getJwtId(): string {
        return $this->JwtId;
    }

    public function setJwtId(string $JwtId): Payload {
        $this->JwtId = $JwtId;
        return $this;
    }

}