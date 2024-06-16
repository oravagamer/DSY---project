<?php

use oravix\HTTP\ContentType;
use oravix\HTTP\Controller;
use oravix\HTTP\HttpStates;
use oravix\HTTP\input\FileUpload;
use oravix\HTTP\input\Json;
use oravix\HTTP\input\multipart\File;
use oravix\HTTP\input\multipart\FormData;
use oravix\HTTP\input\PathVariable;
use oravix\HTTP\Request;
use oravix\security\JOSE\Security;
use oravix\security\Secure;
use oravix\security\SecurityUserId;

require_once "./oravix/db/Database.php";
require_once "./oravix/HTTP/input/multipart/File.php";
require_once "./oravix/HTTP/input/multipart/FormData.php";
require_once "./oravix/HTTP/input/multipart/InputData.php";
require_once "./oravix/HTTP/input/FileUpload.php";
require_once "./oravix/HTTP/input/Json.php";
require_once "./oravix/HTTP/input/JsonValue.php";
require_once "./oravix/HTTP/input/PathVariable.php";
require_once "./oravix/HTTP/Consumes.php";
require_once "./oravix/HTTP/ContentType.php";
require_once "./oravix/HTTP/Controller.php";
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
require_once "./oravix/security/JOSE/JWS.php";
require_once "./oravix/security/JOSE/JWT.php";
require_once "./oravix/security/JOSE/KeyTypes.php";
require_once "./oravix/security/JOSE/Payload.php";
require_once "./oravix/security/JOSE/Security.php";
require_once "./oravix/security/rest/api/data/LoginData.php";
require_once "./oravix/security/rest/api/data/TokensData.php";
require_once "./oravix/security/rest/api/data/RegisterData.php";
require_once "./oravix/security/rest/api/user/User.php";
require_once "./oravix/security/rest/api/user/Users.php";
require_once "./oravix/security/rest/api/user/UserUpdateData.php";
require_once "./oravix/security/rest/api/SecurityHttpActions.php";
require_once "./oravix/security/Secure.php";
require_once "./oravix/security/SecurityUserId.php";

error_reporting(E_ERROR | E_PARSE);

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


$searchPath = "../src/*";
$searchedDirs = glob($searchPath);
$searchedFiles = glob($searchPath . ".php");
while (sizeof($searchedFiles) !== 0 || sizeof($searchedDirs) !== 0) {
    if (sizeof($searchedFiles) !== 0) {
        foreach ($searchedFiles as $file) {
            require $file;
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
                            $paths[$methodAttribute->getArguments()[1]->value . ">" . $_ENV["settings"]["APPLICATION_PATH"] . $classAttribute->getArguments()[0] . $methodAttribute->getArguments()[0]] = $method;
                        }
                    }
                }
            }
        }
    } catch (Exception|Error $e) {
        header("Content-Type", ContentType::TEXT_PLAIN->value);
        statusExit(HttpStates::INTERNAL_SERVER_ERROR, $e->getMessage());
    }
}

$getPathDataKey = $_SERVER["REQUEST_METHOD"] . ">" . $requestedUrl;
$method = $_SERVER["REQUEST_METHOD"];

if (isset($paths[$getPathDataKey])) {
    $userId = null;
    foreach ($paths[$getPathDataKey]->getAttributes() as $attribute) {
        $attribute->newInstance();
        if ($attribute->getName() === Secure::class) {
            $userId = (new Security())->secure();
        }

    }

    $methodPrams = [];
    foreach ($paths[$getPathDataKey]->getParameters() as $parameter) {

        foreach ($parameter->getAttributes() as $attribute) {

            if ($attribute->getName() === PathVariable::class) {
                if ($attribute->getArguments()[1] && !isset($urlPartsQuery[$attribute->getArguments()[0]])) {
                    statusExit(HttpStates::BAD_REQUEST, "Please set path variable: " . $attribute->getArguments()[0]);
                } else {
                    $methodPrams[] = isset($urlPartsQuery[$attribute->getArguments()[0]]) ? $urlPartsQuery[$attribute->getArguments()[0]] : null;
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
                $jsonData = json_decode(file_get_contents('php://input'), true);
                $jsonDataRef = new ReflectionClass($parameter->getType()->getName());

                $methodPrams[] = processJson($jsonData, $jsonDataRef->getName());
            } elseif ($attribute->getName() === SecurityUserId::class) {
                $methodPrams[] = $userId;
            } elseif ($attribute->getName() === FileUpload::class) {
                $methodPrams[] = file_get_contents('php://input');
            }
        }
    }

    $classOfMethod = $paths[$getPathDataKey]->getDeclaringClass()->newInstance();


    $response = $paths[$getPathDataKey]->invoke($classOfMethod, ...$methodPrams);
    if (is_null($response)) {
        statusExit(HttpStates::OK);
    } else {
        statusExit($response->getStatus(), is_array($response->getResponse()) ? json_encode($response->getResponse()) : $response->getResponse());
    }

} else {
    statusExit(HTTPStates::NOT_FOUND);
}

function statusExit(HTTPStates $netStatus, ?string $message = null): void {
    echo $message === null ? ($netStatus === HttpStates::OK ? "" : $netStatus->name) : $message;
    http_response_code($netStatus->value);
    exit(0);
}

function processJson(array $data, ?string $className = null): object|array|null {
    $returnData = null;

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
            $propertyAttribute = $property->getAttributes()[0];

            if (($propertyAttribute->getArguments()[1] && isset($data[$propertyAttribute->getArguments()[0]])) || !$propertyAttribute->getArguments()[1]) {
                $finalProperty = new ReflectionProperty($returnData, $property->getName());
                $finalProperty->setAccessible(true);
                if (is_array($data[$propertyAttribute->getArguments()[0]]) && array_key_exists(0, $data[$propertyAttribute->getArguments()[0]])) {
                    $finalProperty->setValue($returnData, processJson($data[$propertyAttribute->getArguments()[0]]));
                } elseif (is_array($data[$propertyAttribute->getArguments()[0]])) {
                    $finalProperty->setValue($returnData, processJson($data[$propertyAttribute->getArguments()[0]], $property->getType()->getName()));
                } else {
                    $finalProperty->setValue($returnData, $data[$propertyAttribute->getArguments()[0]]);
                }
            } else {
                statusExit(HttpStates::BAD_REQUEST, "Please set value: " . $propertyAttribute->getArguments()[0]);
            }
        }
    }

    return $returnData;
}
