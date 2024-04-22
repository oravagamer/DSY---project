package security;

public enum JWA: int {
    case none = 0;
    case HS256 = 1;
    case HS384 = 2;
    case HS512 = 3;
    case RS256 = 4;
    case RS384 = 5;
    case RS512 = 6;
    case ES256 = 7;
    case ES384 = 8;
    case ES512 = 9;
    case PS256 = 10;
    case PS384 = 11;
    case PS512 = 12;
}