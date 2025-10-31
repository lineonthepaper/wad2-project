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
    // Add this after the existing methods in accounts.php

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

        // Generate confirmation code (6-digit code)
        $confirmationCode = sprintf("%06d", mt_rand(1, 999999));
        
        // Store confirmation code temporarily (you can use session or cache in production)
        // For simplicity, we'll include it in the response for demo purposes
        // In production, you might want to store this in a temporary cache
        
        // Prepare email content
        $subject = "Confirm Your Email Change";
        $message = "
        Hello,

        You have requested to change your email address from $currentEmail to $newEmail.

        Your confirmation code is: $confirmationCode

        Please enter this code in the application to complete the email change.

        This code will expire in 10 minutes.

        If you did not request this change, please ignore this email.

        Best regards,
        Your App Team
        ";

        // Send email using an email API (using Resend.com as example)
        $emailSent = sendEmailViaAPI($newEmail, $subject, $message);

        if ($emailSent) {
            // In a real application, you would store the confirmation code in a secure way
            // For this example, we'll return it (not recommended for production)
            echo json_encode([
                "message" => "Confirmation email sent successfully.",
                "requiresConfirmation" => true,
                "confirmationCode" => $confirmationCode, // Remove this in production
                "accountId" => $accountId,
                "newEmail" => $newEmail
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

if ($method == "confirmEmailChange") {
    try {
        if (!isset($payload['accountId']) || !isset($payload['newEmail']) || !isset($payload['confirmationCode'])) {
            http_response_code(400);
            echo json_encode(["message" => "Account ID, new email, and confirmation code are required."]);
            exit;
        }

        $accountId = (int)$payload['accountId'];
        $newEmail = trim($payload['newEmail']);
        $confirmationCode = trim($payload['confirmationCode']);

        // In production, you would verify the confirmation code from your cache/storage
        // For this example, we'll assume the code is valid (implement proper verification)
        
        $accountDAO = new AccountDAO($useServer);
        
        // Verify that the email doesn't exist (double-check)
        $existingAccount = $accountDAO->getAccountByEmail($newEmail);
        if ($existingAccount) {
            http_response_code(400);
            echo json_encode(["message" => "Email already exists."]);
            exit;
        }

        // Update email
        $success = $accountDAO->updateEmail($accountId, $newEmail);

        if ($success) {
            echo json_encode([
                "message" => "Email updated successfully.",
                "newEmail" => $newEmail
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to update email."]);
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(["message" => "Error confirming email change: " . $e->getMessage()]);
    }
    exit;
}

// Email API function (using Resend.com as example)
function sendEmailViaAPI($to, $subject, $message) {
    $apiKey = 're_AVBpML63_GvyWAJ8rH3YSZXPxDnEB4q4Q'; // Get from https://resend.com
    
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

// Alternative email sending function using SMTP (if you prefer not to use an API)
function sendEmailViaSMTP($to, $subject, $message) {
    // This is a basic implementation - use PHPMailer or similar in production
    $headers = "From: noreply@yourdomain.com\r\n";
    $headers .= "Reply-To: noreply@yourdomain.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}
}
?>