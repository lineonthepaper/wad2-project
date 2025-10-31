<?php

require_once __DIR__ . "/common.php";

header("Content-Type: application/json");

$method = $_SERVER["REQUEST_METHOD"];

$useServer = true;

// Email API function (using Resend.com as example)
function sendEmailViaAPI($to, $subject, $message) {
    $apiKey = 're_AVBpML63_GvyWAJ8rH3YSZXPxDnEB4q4Q';
    
    $data = [
        'from' => 'Your App <noreply@yourdomain.com>',
        'to' => [$to],
        'subject' => $subject,
        'text' => $message
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.resend.com/emails');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode === 200;
}

if ($method === "POST") {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) $payload = [];

    $method = $payload['method'] ?? '';

    if ($method == "addAccount") {
        try {
            // Server-side password validation
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

            // Check if email already exists
            $accountDAO = new AccountDAO($useServer);
            $existingAccount = $accountDAO->getAccountByEmail($payload['email']);
            if ($existingAccount) {
                http_response_code(400);
                echo json_encode(["message" => "Email already exists."]);
                exit;
            }

            $success = $accountDAO->addAccount(
                new Account(
                    null,
                    $payload['displayName'],
                    $payload['email'],
                    Account::hashPassword($payload['password']),
                    (bool)($payload['isStaff'] ?? false)
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
            
            // Verify password using the existing method in AccountDAO
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

    if ($method == "changeEmail") {
        try {
            if (!isset($payload['accountId']) || !isset($payload['newEmail']) || !isset($payload['currentEmail'])) {
                http_response_code(400);
                echo json_encode(["message" => "Account ID, current email, and new email are required."]);
                exit;
            }

            $accountId = (int)$payload['accountId'];
            $newEmail = trim($payload['newEmail']);
            $currentEmail = trim($payload['currentEmail']);

            // Validate email format
            if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(["message" => "Invalid email format."]);
                exit;
            }

            // Check if new email already exists
            $accountDAO = new AccountDAO($useServer);
            $existingAccount = $accountDAO->getAccountByEmail($newEmail);
            if ($existingAccount) {
                http_response_code(400);
                echo json_encode(["message" => "Email already exists."]);
                exit;
            }

            // Generate confirmation token
            $confirmationToken = bin2hex(random_bytes(32));
            
            // Store token in session (valid for 1 hour)
            session_start();
            $_SESSION['email_confirmation'] = [
                'token' => $confirmationToken,
                'accountId' => $accountId,
                'newEmail' => $newEmail,
                'expires' => time() + 3600 // 1 hour
            ];
            
            // Create confirmation link
            $confirmationLink = "https://yourdomain.com/confirmemail.php?token=" . $confirmationToken;
            
            // Prepare email content
            $subject = "Confirm Your Email Change";
            $message = "
            Hello,

            You have requested to change your email address from $currentEmail to $newEmail.

            Please click the following link to confirm this change:
            $confirmationLink

            This link will expire in 1 hour.

            If you did not request this change, please ignore this email.

            Best regards,
            Your App Team
            ";

            // Send email using the API
            $emailSent = sendEmailViaAPI($newEmail, $subject, $message);

            if ($emailSent) {
                echo json_encode([
                    "message" => "Confirmation email sent successfully. Please check your new email address.",
                    "requiresConfirmation" => true
                ]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Failed to send confirmation email."]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Error changing email: " . $e->getMessage()]);
        }
        exit;
    }
}
?>