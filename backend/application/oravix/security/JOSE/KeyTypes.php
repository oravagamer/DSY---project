<?php

namespace oravix\security\JOSE;

enum KeyTypes: string {
    case OCT = "oct";
    case RSA = "RSA";
    case EC = "EC";
    case OKP = "OKP";
}
