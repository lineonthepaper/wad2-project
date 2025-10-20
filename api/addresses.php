<?php

require_once __DIR__ . "/common.php";

header("Content-Type: application/json");

$method = $_SERVER["REQUEST_METHOD"];

$useServer = true;

if ($method === "POST") {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) $payload = [];

    $method = $payload['method'] ?? '';

    if ($method == "addAddress") {
        try {
            $mailDAO = new MailDAO($useServer);
            $addressId = $mailDAO->addAddress(new Address(
                null,
                $payload["name"],
                $payload["email"],
                $payload["phone"],
                $payload["phoneCountryCode"],
                $payload["address"]
            ));
            if ($addressId !== false) {
                $message = "Address created successfully.";
                $success = true;
                echo json_encode(["message" => $message, "success" => $success, "addressId" => $addressId]);
                exit;
            } else {
                $message = "Error in address creation.";
                $success = false;
                echo json_encode(["message" => $message, "success" => $success]);
                exit;
            }
            exit;
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Caught exception " . $e->getMessage(), "success" => false]);
            exit;
        }
    } elseif ($method == "getAddressById") {
        try {
            $mailDAO = new MailDAO($useServer);
            $address = $mailDAO->getAddressById($payload["addressId"]);
            if ($address) {
                echo json_encode([
                    "message" => "Found address.",
                    "address" => $address->jsonSerialize()
                ]);
                exit;
            } else {
                echo json_encode(["message" => "Address could not be found."]);
                exit;
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Caught exception " . $e->getMessage()]);
            exit;
        }
    }
}
