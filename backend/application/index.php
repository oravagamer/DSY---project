<?php

use oravix\exceptions\HttpException;
use oravix\HTTP\ContentType;
use oravix\HTTP\Controller;
use oravix\HTTP\HttpStates;
use oravix\HTTP\input\FileUpload;
use oravix\HTTP\input\HeaderInput;
use oravix\HTTP\input\Json;
use oravix\HTTP\input\multipart\File;
use oravix\HTTP\input\multipart\FormData;
use oravix\HTTP\input\PageInput;
use oravix\HTTP\input\PageInputParams;
use oravix\HTTP\input\PathVariable;
use oravix\HTTP\input\PlainText;
use oravix\HTTP\Request;
use oravix\security\JOSE\Security;
use oravix\security\Secure;
use oravix\security\SecurityUserId;

require_once "./oravix/db/Database.php";
require_once "./oravix/exceptions/HttpException.php";
require_once "./oravix/HTTP/input/multipart/File.php";
require_once "./oravix/HTTP/input/multipart/FormData.php";
require_once "./oravix/HTTP/input/multipart/InputData.php";
require_once "./oravix/HTTP/input/FileUpload.php";
require_once "./oravix/HTTP/input/HeaderInput.php";
require_once "./oravix/HTTP/input/Json.php";
require_once "./oravix/HTTP/input/JsonValue.php";
require_once "./oravix/HTTP/input/PageInput.php";
require_once "./oravix/HTTP/input/PageInputParams.php";
require_once "./oravix/HTTP/input/PathVariable.php";
require_once "./oravix/HTTP/input/PlainText.php";
require_once "./oravix/HTTP/Consumes.php";
require_once "./oravix/HTTP/ContentType.php";
require_once "./oravix/HTTP/Controller.php";
require_once "./oravix/HTTP/EncryptedURL.php";
require_once "./oravix/HTTP/HttpHeader.php";
require_once "./oravix/HTTP/HttpMethod.php";
require_once "./oravix/HTTP/HttpResponse.php";
require_once "./oravix/HTTP/HttpStates.php";
require_once "./oravix/HTTP/Produces.php";
require_once "./oravix/HTTP/Request.php";
require_once "./oravix/security/JOSE/Algorithm.php";
require_once "./oravix/security/JOSE/AlgorithmFamily.php";
require_once "./oravix/security/JOSE/Header.php";
require_once "./oravix/security/JOSE/JWA.php";
require_once "./oravix/security/JOSE/JWT.php";
require_once "./oravix/security/JOSE/Payload.php";
require_once "./oravix/security/JOSE/Security.php";
require_once "./oravix/security/rest/api/data/LoginData.php";
require_once "./oravix/security/rest/api/data/TokensData.php";
require_once "./oravix/security/rest/api/data/RegisterData.php";
require_once "./oravix/security/rest/api/role/Role.php";
require_once "./oravix/security/rest/api/role/RoleData.php";
require_once "./oravix/security/rest/api/role/RoleUpdateData.php";
require_once "./oravix/security/rest/api/user/User.php";
require_once "./oravix/security/rest/api/user/Users.php";
require_once "./oravix/security/rest/api/user/UserUpdateData.php";
require_once "./oravix/security/rest/api/SecurityHttpActions.php";
require_once "./oravix/security/Secure.php";
require_once "./oravix/security/SecurityUserId.php";

error_reporting(E_ERROR | E_PARSE);
session_start();
session_regenerate_id();

function statusExit(HTTPStates $netStatus, ?string $message = null): void {
    echo $message;
    http_response_code($netStatus->value);
    exit(0);
}

function decrypt(string $data, string $nonce, string $keypair): string {
    try {
        $decrypted = sodium_crypto_box_open(sodium_base642bin($data, SODIUM_BASE64_VARIANT_ORIGINAL), $nonce, $keypair);
        if (!is_bool($decrypted)) {
            return $decrypted;
        } else {
            statusExit(HttpStates::I_AM_A_TEAPOT);
        }
    } catch (SodiumException $e) {
        echo $e->getMessage();
        statusExit(HttpStates::I_AM_A_TEAPOT);
    }
}

function processJson(array $data, ?string $className = null): object|array|null {
    $returnData = [];

    if (is_null($className)) {
        foreach ($data as $value) {
            if (is_array($value)) {
                $returnData[] = processJson($value);
            } elseif (is_object($value)) {
                $returnData[] = processJson($value, (new ReflectionClass($value))->getName());
            } else {
                $returnData[] = $value;
            }
        }
    } else {
        $JsonClassRef = new ReflectionClass($className);
        $returnData = $JsonClassRef->newInstance();

        foreach ($JsonClassRef->getProperties() as $property) {
            $propertyAttributeInstance = $property->getAttributes()[0]->newInstance();

            if (($propertyAttributeInstance->required && isset($data[$propertyAttributeInstance->name])) || !$propertyAttributeInstance->required) {
                $finalProperty = new ReflectionProperty($returnData, $property->getName());
                $finalProperty->setAccessible(true);
                if (is_array($data[$propertyAttributeInstance->name]) && array_key_exists(0, $data[$propertyAttributeInstance->name])) {
                    $finalProperty->setValue($returnData, processJson($data[$propertyAttributeInstance->name]));
                } elseif (is_array($data[$propertyAttributeInstance->name])) {
                    $finalProperty->setValue($returnData, processJson($data[$propertyAttributeInstance->name], $property->getType()->getName()));
                } else {
                    $stringedValue = strval($data[$propertyAttributeInstance->name]);
                    if (!preg_match($propertyAttributeInstance->regex, $stringedValue)) {
                        statusExit(HttpStates::BAD_REQUEST, $propertyAttributeInstance->name . " does not match pattern \"" . $propertyAttributeInstance->regex . "\"");
                    }
                    $finalProperty->setValue($returnData, $data[$propertyAttributeInstance->name]);
                }
            } else {
                statusExit(HttpStates::BAD_REQUEST, "Please set value: " . $propertyAttributeInstance->name);
            }
        }
    }

    return $returnData;
}

try {
    $rootPath = "/DSY---project/backend";
    $requestedUrl = explode($rootPath, $_SERVER["REQUEST_URI"])[1];
    $pathParameters = "";
    $_ENV["settings"] = parse_ini_file("../settings.env");

    if (str_contains($requestedUrl, "/?")) {
        list($requestedUrl, $pathParameters) = explode("/?", $requestedUrl, 2);
    } elseif (str_contains($requestedUrl, "?")) {
        list($requestedUrl, $pathParameters) = explode("?", $requestedUrl, 2);
    }

    if ($requestedUrl[strlen($requestedUrl) - 1] === "/") {
        $requestedUrl = substr($requestedUrl, 0, strlen($requestedUrl) - 1);
    }

    parse_str(parse_url("http://test/?" . $pathParameters)["query"], $urlPartsQuery);
    $encrypted = $urlPartsQuery["encryption"] === "1";
    if ($encrypted) {
        $nonce = apache_request_headers()["nonce"];
        if (is_null($nonce)) {
            statusExit(HttpStates::BAD_REQUEST, "Please header: nonce");
        }
        $nonce = sodium_base642bin($nonce, SODIUM_BASE64_VARIANT_ORIGINAL);
        $encryptionKeypair = $_SESSION["keypair"];
    }

    if (!is_null($urlPartsQuery["nonce"])) {
        $encrypted = true;
        $encryptionKeypair = $_SESSION["keypair"];
        $nonce = sodium_base642bin($urlPartsQuery["nonce"], SODIUM_BASE64_VARIANT_ORIGINAL);
    }

    $searchPath = "../src/*";
    $searchedDirs = glob($searchPath);
    $searchedFiles = glob($searchPath . ".php");
    while (sizeof($searchedFiles) !== 0 || sizeof($searchedDirs) !== 0) {
        if (sizeof($searchedFiles) !== 0) {
            foreach ($searchedFiles as $file) {
                require_once $file;
            }
        }
        $searchPath = $searchPath . "/*";
        $searchedDirs = glob($searchPath);
        $searchedFiles = glob($searchPath . ".php");
    }

    $paths = [];

    foreach (get_declared_classes() as $class) {
        try {
            $refClass = new ReflectionClass($class);

            foreach ($refClass->getAttributes() as $classAttribute) {
                if ($classAttribute->getName() === Controller::class) {
                    foreach ($refClass->getMethods() as $method) {
                        foreach ($method->getAttributes() as $methodAttribute) {
                            if ($methodAttribute->getName() === Request::class) {
                                $paths[$methodAttribute->getArguments()[1]->value][$_ENV["settings"]["APPLICATION_PATH"] . $classAttribute->getArguments()[0] . $methodAttribute->getArguments()[0]] = $method;
                            }
                        }
                    }
                }
            }
        } catch (Exception|Error $e) {

            header("content-type", ContentType::TEXT_PLAIN->value);
            statusExit(HttpStates::INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }
    $method = $_SERVER["REQUEST_METHOD"];
    $payload = file_get_contents('php://input');

    if (isset($paths[$method][$requestedUrl])) {
        foreach ($paths[$method][$requestedUrl]->getAttributes() as $attribute) {
            if ($attribute->getName() === Secure::class) {
                $_ENV["data"]["user-id"] = (new Security())->secure();
            }
            $attribute->newInstance();

        }

        $methodPrams = [];
        foreach ($paths[$method][$requestedUrl]->getParameters() as $parameter) {

            foreach ($parameter->getAttributes() as $attribute) {
                $attributeInitialized = $attribute->newInstance();

                if ($attribute->getName() === PathVariable::class) {
                    if ($attributeInitialized->required && !isset($urlPartsQuery[$attributeInitialized->name])) {
                        statusExit(HttpStates::BAD_REQUEST, "Please set path variable: " . $attributeInitialized->name);
                    } else {
                        $localValue = isset($urlPartsQuery[$attributeInitialized->name]) ? ($encrypted ? decrypt($urlPartsQuery[$attributeInitialized->name], $nonce, $encryptionKeypair) : $urlPartsQuery[$attributeInitialized->name]) : $parameter->getDefaultValue();
                        if (!preg_match($attributeInitialized->regex, strval($localValue))) {
                            //var_dump($localValue);
                            statusExit(HttpStates::BAD_REQUEST, $attributeInitialized->name . " does not match pattern \"" . $attributeInitialized->regex . "\"");
                        }
                        $methodPrams[] = $localValue;
                    }
                } elseif ($attribute->getName() === FormData::class) {
                    $formDataRef = new ReflectionClass($parameter->getType()->getName());
                    $formData = $formDataRef->newInstance();

                    foreach ($formDataRef->getProperties() as $formDataProperty) {
                        $formDataPropertyAttribute = $formDataProperty->getAttributes()[0];
                        $propertyName = $formDataPropertyAttribute->getArguments()[0];
                        if (($formDataPropertyAttribute->getArguments()[1] && (isset($_FILES[$propertyName]) || isset($_POST[$propertyName]) || isset($_GET[$propertyName]))) || !$formDataPropertyAttribute->getArguments()[1]) {
                            $property = new ReflectionProperty($formData, $formDataProperty->getName());
                            $property->setAccessible(true);

                            if (!is_null($formDataPropertyAttribute->getArguments()[2])) {
                                $files = [];
                                if (is_array($_FILES[$propertyName]["name"])) {
                                    for ($i = 0; $i < sizeof($_FILES[$propertyName]["name"]); $i++) {
                                        $files[] = new File(
                                            $_FILES[$propertyName]["name"][$i],
                                            $_FILES[$propertyName]["full_path"][$i],
                                            $_FILES[$propertyName]["type"][$i],
                                            $_FILES[$propertyName]["tmp_name"][$i],
                                            $_FILES[$propertyName]["error"][$i],
                                            $_FILES[$propertyName]["size"][$i]
                                        );
                                    }
                                } elseif (isset($_FILES[$propertyName]["name"])) {
                                    $files[] = new File(
                                        $_FILES[$propertyName]["name"],
                                        $_FILES[$propertyName]["full_path"],
                                        $_FILES[$propertyName]["type"],
                                        $_FILES[$propertyName]["tmp_name"],
                                        $_FILES[$propertyName]["error"],
                                        $_FILES[$propertyName]["size"]
                                    );
                                } else {
                                    $files = null;
                                }

                                $property->setValue($formData, $files);
                            } else {
                                if (isset($_POST[$propertyName])) {
                                    $property->setValue($formData, $_POST[$propertyName]);
                                } else {
                                    $property->setValue($formData, $_GET[$propertyName]);
                                }
                            }
                        } else {
                            statusExit(HttpStates::BAD_REQUEST, "Please set parameter: " . $formDataPropertyAttribute->getArguments()[0]);
                        }
                    }

                    $methodPrams[] = $formData;

                } elseif ($attribute->getName() === Json::class) {
                    $jsonData = json_decode($encrypted ? decrypt($payload, $nonce, $encryptionKeypair) : $payload, true);
                    $jsonDataRef = new ReflectionClass($parameter->getType()->getName());
                    $methodPrams[] = processJson($jsonData, $jsonDataRef->getName());
                } elseif ($attribute->getName() === SecurityUserId::class) {
                    $methodPrams[] = $_ENV["data"]["user-id"];
                } elseif ($attribute->getName() === FileUpload::class) {
                    $methodPrams[] = $encrypted ? decrypt($payload, $nonce, $encryptionKeypair) : $payload;
                } elseif ($attribute->getName() === PlainText::class) {
                    $localValue = $encrypted ? decrypt($payload, $nonce, $encryptionKeypair) : $payload;
                    if (!preg_match($attributeInitialized->regex, strval($localValue))) {
                        statusExit(HttpStates::BAD_REQUEST, "Input does not match pattern \"" . $attributeInitialized->regex . "\"");
                    }
                    $methodPrams[] = $localValue;
                } elseif ($attribute->getName() === HeaderInput::class) {
                    $methodPrams[] = apache_request_headers()[$attribute->getArguments()[0]];
                } elseif ($attribute->getName() === PageInputParams::class) {
                    $inputParams = $attribute->newInstance();
                    if (!isset($urlPartsQuery["page"])) {
                        statusExit(HttpStates::BAD_REQUEST, "Please set variable: page");
                    } else if (!isset($urlPartsQuery["count"])) {
                        statusExit(HttpStates::BAD_REQUEST, "Please set variable: count");
                    }
                    $sortBy = "";
                    $asc = $inputParams->ascending;
                    if (isset($urlPartsQuery["sort-by"])) {
                        $sortBy = $encrypted ? decrypt($urlPartsQuery["sort-by"], $nonce, $encryptionKeypair) : $urlPartsQuery["sort-by"];
                    }
                    if (isset($urlPartsQuery["asc"])) {
                        $asc = $encrypted ? decrypt($urlPartsQuery["asc"], $nonce, $encryptionKeypair) : $urlPartsQuery["asc"];
                    }
                    $page = $encrypted ? decrypt($urlPartsQuery["page"], $nonce, $encryptionKeypair) : $urlPartsQuery["page"];
                    $count = $encrypted ? decrypt($urlPartsQuery["count"], $nonce, $encryptionKeypair) : $urlPartsQuery["count"];
                    $asc = $encrypted ? decrypt($urlPartsQuery["asc"], $nonce, $encryptionKeypair) : $urlPartsQuery["asc"];
                    if (!in_array($sortBy, $inputParams->allowedColumns) && $inputParams->allowedColumns !== []) {
                        statusExit(HttpStates::BAD_REQUEST, "Please use existing column");
                    }
                    $pageInput = new PageInput($sortBy !== "" ? $sortBy : $inputParams->defaultSortBy, $page, $count, $asc);
                    $methodPrams[] = $pageInput;
                }
            }
        }

        $classOfMethod = $paths[$method][$requestedUrl]->getDeclaringClass()->newInstance();


        try {
            $response = $paths[$method][$requestedUrl]->invoke($classOfMethod, ...$methodPrams);
        } catch (Throwable|HttpException $e) {
            if ($e instanceof HttpException) {
                $response = new \oravix\HTTP\HttpResponse($e->getMessage(), $e->getState());
            } else {
                $response = new \oravix\HTTP\HttpResponse($e->getMessage(), HttpStates::INTERNAL_SERVER_ERROR);
            }
        }
        if (is_null($response)) {
            statusExit(HttpStates::OK);
        } else {
            if ($encrypted) {
                $newNonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
                header("nonce: " . sodium_bin2base64($newNonce, SODIUM_BASE64_VARIANT_ORIGINAL));
                statusExit(
                    $response->getStatus(),
                    sodium_bin2base64(is_array($response->getResponse()) ? sodium_crypto_box(json_encode($response->getResponse()), $newNonce, $encryptionKeypair) : sodium_crypto_box($response->getResponse(), $newNonce, $encryptionKeypair), SODIUM_BASE64_VARIANT_ORIGINAL)
                );
            }
            statusExit(
                $response->getStatus(),
                is_array($response->getResponse()) ? json_encode($response->getResponse()) : $response->getResponse()
            );
        }

    } else {
        statusExit(HTTPStates::NOT_FOUND);
    }
} catch (Throwable $e) {
    var_dump($e);
}
