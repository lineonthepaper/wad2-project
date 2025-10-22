<?php

require_once __DIR__ . "/common.php";

header("Content-Type: application/json");

$method = $_SERVER["REQUEST_METHOD"];

$useServer = true;

if ($method === "POST") {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) $payload = [];

    $method = $payload['method'] ?? '';

    if ($method == "addAccount") {
    try {
        if (strlen($payload['password']) < 8) {
            http_response_code(400);
            echo json_encode(["message" => "Password must be at least 8 characters long."]);
            exit;
        }
        
        $accountDAO = new AccountDAO($useServer);
        $success = $accountDAO->addAccount(
            new Account(
                null,
                $payload['displayName'],
                $payload['email'],
                Account::hashPassword($payload['password']),
                $payload['isStaff'] == true
            )
        );
        if ($success) {
            echo json_encode(["message" => "Account created successfully."]);
            exit;
        } else {
            echo json_encode(["message" => "Error in account creation."]);
            exit;
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(["message" => "Caught exception " . $e->getMessage()]);
        exit;
    }
}

    if ($method == "getAccountById") {
        try {
            $accountDAO = new AccountDAO($useServer);
            $account = $accountDAO->getAccountById($payload["accountId"]);
            if ($account) {
                echo json_encode([
                    "message" => "Found account.",
                    "account" => $account->jsonSerialize()
                ]);
                exit;
            } else {
                echo json_encode(["message" => "Account could not be found."]);
                exit;
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Caught exception " . $e->getMessage()]);
            exit;
        }
    }

    if ($method == "getAccountByEmail") {
        try {
            $accountDAO = new AccountDAO($useServer);
            $account = $accountDAO->getAccountByEmail($payload["email"]);
            if ($account) {
                echo json_encode([
                    "message" => "Found account.",
                    "account" => $account->jsonSerialize()
                ]);
                exit;
            } else {
                echo json_encode(["message" => "Account could not be found."]);
                exit;
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Caught exception " . $e->getMessage()]);
            exit;
        }
    }
}
