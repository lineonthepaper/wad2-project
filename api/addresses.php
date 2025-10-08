<?php

require_once __DIR__ . "/common.php";

header("Content-Type: application/json");

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "POST") {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) $payload = [];

    $method = $payload['method'] ?? '';

    if ($method == "addAddress") {
        try {
            $mailDAO = new MailDAO();
            $success = $mailDAO->addAddress(new Address(
                null,
                $payload["name"],
                $payload["email"],
                $payload["phone"],
                $payload["phoneCountryCode"],
                $payload["address"]
            ));
            if ($success) {
                echo json_encode(
                    ["message" => "Address created successfully."]
                );
                exit;
            } else {
                echo json_encode(["message" => "Error in address creation."]);
                exit;
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Caught exception " . $e->getMessage()]);
            exit;
        }
    } elseif ($method == "getAddressById") {
        try {
            $mailDAO = new MailDAO();
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
