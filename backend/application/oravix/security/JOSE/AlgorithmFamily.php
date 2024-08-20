<?php

namespace oravix\security\JOSE;

enum AlgorithmFamily: string {
    case NONE = "none";
    case HS = "HS";
    case RS = "BS";
    case ES = "ES";
    case PS = "PS";
}
