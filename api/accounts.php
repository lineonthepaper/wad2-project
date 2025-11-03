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
        if (!isset($payload['password']) || !isset($payload['confirmPassword'])) {
            http_response_code(400);
            echo json_encode(["message" => "Password and confirmation are required."]);
            exit;
        }

        if ($payload['password'] !== $payload['confirmPassword']) {
            http_response_code(400);
            echo json_encode(["message" => "Passwords do not match."]);
            exit;
        }

        if (strlen($payload['password']) < 6) {
            http_response_code(400);
            echo json_encode(["message" => "Password must be at least 6 characters long."]);
            exit;
        }

        $accountDAO = new AccountDAO($useServer);
        $existingAccount = $accountDAO->getAccountByEmail($payload['email']);
        if ($existingAccount) {
            http_response_code(400);
            echo json_encode(["message" => "Email already exists."]);
            exit;
        }

        $isStaff = $payload['isStaff'] ?? false;

        if (is_string($isStaff)) {
            $isStaff = ($isStaff === 'true' || $isStaff === '1');
        } else {
            $isStaff = (bool)$isStaff;
        }

        $success = $accountDAO->addAccount(
            new Account(
                null,
                $payload['displayName'],
                $payload['email'],
                Account::hashPassword($payload['password']),
                $isStaff
            )
        );
        if ($success) {
            echo json_encode(["message" => "Account created successfully."]);
            exit;
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error in account creation."]);
            exit;
        }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Caught exception: " . $e->getMessage()]);
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

    if ($method == "loginAccount") {
        try {
            if (!isset($payload['email']) || !isset($payload['password'])) {
                http_response_code(400);
                echo json_encode(["message" => "Email and password are required."]);
                exit;
            }

            $accountDAO = new AccountDAO($useServer);
            
            if ($accountDAO->verifyPassword($payload['email'], $payload['password'])) {
                $account = $accountDAO->getAccountByEmail($payload['email']);
                if ($account) {
                    echo json_encode([
                        "message" => "Login successful.",
                        "account" => $account->jsonSerialize()
                    ]);
                    exit;
                } else {
                    http_response_code(500);
                    echo json_encode(["message" => "Error retrieving account information."]);
                    exit;
                }
            } else {
                http_response_code(401);
                echo json_encode(["message" => "Invalid email or password."]);
                exit;
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Caught exception: " . $e->getMessage()]);
            exit;
        }
    }

    if ($method == "updateDisplayName") {
        try {
            if (!isset($payload['accountId']) || !isset($payload['newDisplayName'])) {
                http_response_code(400);
                echo json_encode(["message" => "Account ID and new display name are required."]);
                exit;
            }

            $accountId = (int)$payload['accountId'];
            $newDisplayName = trim($payload['newDisplayName']);

            if (empty($newDisplayName)) {
                http_response_code(400);
                echo json_encode(["message" => "Display name cannot be empty."]);
                exit;
            }

            $accountDAO = new AccountDAO($useServer);
            $success = $accountDAO->updateDisplayName($accountId, $newDisplayName);

            if ($success) {
                echo json_encode(["message" => "Display name updated successfully."]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Failed to update display name."]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Error updating display name: " . $e->getMessage()]);
        }
        exit;
    }

    if ($method == "updatePassword") {
        try {
            if (!isset($payload['accountId']) || !isset($payload['newPassword'])) {
                http_response_code(400);
                echo json_encode(["message" => "Account ID and new password are required."]);
                exit;
            }

            $accountId = (int)$payload['accountId'];
            $newPassword = $payload['newPassword'];

            if (strlen($newPassword) < 6) {
                http_response_code(400);
                echo json_encode(["message" => "Password must be at least 6 characters long."]);
                exit;
            }

            $accountDAO = new AccountDAO($useServer);
            $newPasswordHashed = Account::hashPassword($newPassword);
            $success = $accountDAO->updatePassword($accountId, $newPasswordHashed);

            if ($success) {
                echo json_encode(["message" => "Password updated successfully."]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Failed to update password."]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Error updating password: " . $e->getMessage()]);
        }
        exit;
    }

    if ($method == "verifyPassword") {
        try {
            if (!isset($payload['email']) || !isset($payload['password'])) {
                http_response_code(400);
                echo json_encode(["message" => "Email and password are required."]);
                exit;
            }

            $accountDAO = new AccountDAO($useServer);
            $isValid = $accountDAO->verifyPassword($payload['email'], $payload['password']);

            echo json_encode([
                "valid" => $isValid,
                "message" => $isValid ? "Password is valid" : "Password is invalid"
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Error verifying password: " . $e->getMessage()]);
        }
        exit;
    }

    if ($method == "updateEmail") {
        try {
            if (!isset($payload['accountId']) || !isset($payload['newEmail'])) {
                http_response_code(400);
                echo json_encode(["message" => "Account ID and new email are required."]);
                exit;
            }

            $accountId = (int)$payload['accountId'];
            $newEmail = trim($payload['newEmail']);

            if (empty($newEmail) || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(["message" => "Please provide a valid email address."]);
                exit;
            }

            $accountDAO = new AccountDAO($useServer);
            $existingAccount = $accountDAO->getAccountByEmail($newEmail);
            if ($existingAccount && $existingAccount->getAccountId() !== $accountId) {
                http_response_code(400);
                echo json_encode(["message" => "Email already exists."]);
                exit;
            }

            $success = $accountDAO->updateEmail($accountId, $newEmail);

            if ($success) {
                echo json_encode(["message" => "Email updated successfully."]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Failed to update email."]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Error updating email: " . $e->getMessage()]);
        }
        exit;
    }

    if ($method == "deleteAccount") {
        try {
            if (!isset($payload['accountId']) || !isset($payload['password'])) {
                http_response_code(400);
                echo json_encode(["message" => "Account ID and password are required."]);
                exit;
            }

            $accountId = (int)$payload['accountId'];
            $password = $payload['password'];

            $accountDAO = new AccountDAO($useServer);
            
            $account = $accountDAO->getAccountById($accountId);
            if (!$account) {
                http_response_code(404);
                echo json_encode(["message" => "Account not found."]);
                exit;
            }

            if (!$accountDAO->verifyPassword($account->getEmail(), $password)) {
                http_response_code(401);
                echo json_encode(["message" => "Invalid password."]);
                exit;
            }

            $success = $accountDAO->deleteAccount($accountId);

            if ($success) {
                echo json_encode(["message" => "Account deleted successfully."]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Failed to delete account."]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Error deleting account: " . $e->getMessage()]);
        }
        exit;
    }
}
?>