<?php
require_once __DIR__ . "/common.php";

session_start();

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $token = $_GET['token'] ?? '';
    
    if (empty($token)) {
        die("
        <!DOCTYPE html>
        <html>
        <head>
            <title>Email Confirmation Failed</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
            <style>
                body { background-color: #f8f9fa; }
                .container { max-width: 600px; margin-top: 100px; }
                .alert { border-radius: 10px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='alert alert-danger text-center'>
                    <h2>Invalid Confirmation Link</h2>
                    <p>The confirmation link is invalid or missing.</p>
                    <a href='/login' class='btn btn-primary'>Return to Login</a>
                </div>
            </div>
        </body>
        </html>
        ");
    }

    // Verify token from session
    if (!isset($_SESSION['email_confirmation'])) {
        die("
        <!DOCTYPE html>
        <html>
        <head>
            <title>Email Confirmation Failed</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
            <style>
                body { background-color: #f8f9fa; }
                .container { max-width: 600px; margin-top: 100px; }
                .alert { border-radius: 10px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='alert alert-danger text-center'>
                    <h2>Confirmation Link Expired</h2>
                    <p>No pending email change request found or the link has expired.</p>
                    <p>Please request a new email change confirmation.</p>
                    <a href='/login' class='btn btn-primary'>Return to Login</a>
                </div>
            </div>
        </body>
        </html>
        ");
    }

    $confirmationData = $_SESSION['email_confirmation'];
    
    // Check if expired
    if (time() > $confirmationData['expires']) {
        unset($_SESSION['email_confirmation']);
        die("
        <!DOCTYPE html>
        <html>
        <head>
            <title>Email Confirmation Failed</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
            <style>
                body { background-color: #f8f9fa; }
                .container { max-width: 600px; margin-top: 100px; }
                .alert { border-radius: 10px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='alert alert-danger text-center'>
                    <h2>Confirmation Link Expired</h2>
                    <p>The confirmation link has expired. Please request a new one.</p>
                    <a href='/login' class='btn btn-primary'>Return to Login</a>
                </div>
            </div>
        </body>
        </html>
        ");
    }

    // Verify token matches
    if ($confirmationData['token'] !== $token) {
        die("
        <!DOCTYPE html>
        <html>
        <head>
            <title>Email Confirmation Failed</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
            <style>
                body { background-color: #f8f9fa; }
                .container { max-width: 600px; margin-top: 100px; }
                .alert { border-radius: 10px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='alert alert-danger text-center'>
                    <h2>Invalid Confirmation Link</h2>
                    <p>The confirmation link is invalid.</p>
                    <a href='/login' class='btn btn-primary'>Return to Login</a>
                </div>
            </div>
        </body>
        </html>
        ");
    }

    $accountId = $confirmationData['accountId'];
    $newEmail = $confirmationData['newEmail'];

    // Update email in database
    $accountDAO = new AccountDAO(true);
    
    // Final check if email exists
    $existingAccount = $accountDAO->getAccountByEmail($newEmail);
    if ($existingAccount) {
        die("
        <!DOCTYPE html>
        <html>
        <head>
            <title>Email Confirmation Failed</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
            <style>
                body { background-color: #f8f9fa; }
                .container { max-width: 600px; margin-top: 100px; }
                .alert { border-radius: 10px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='alert alert-danger text-center'>
                    <h2>Email Already Exists</h2>
                    <p>The email address $newEmail is already in use.</p>
                    <p>Please try a different email address.</p>
                    <a href='/login' class='btn btn-primary'>Return to Login</a>
                </div>
            </div>
        </body>
        </html>
        ");
    }

    // Update email
    $success = $accountDAO->updateEmail($accountId, $newEmail);

    if ($success) {
        // Clear the confirmation data
        unset($_SESSION['email_confirmation']);
        
        echo "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Email Changed Successfully</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
            <style>
                body { background-color: #f8f9fa; }
                .container { max-width: 600px; margin-top: 100px; }
                .alert { border-radius: 10px; }
                .success-icon { font-size: 4rem; color: #28a745; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='alert alert-success text-center'>
                    <div class='success-icon'>✓</div>
                    <h2>Email Changed Successfully!</h2>
                    <p>Your email address has been updated to: <strong>$newEmail</strong></p>
                    <p>You can now log in with your new email address.</p>
                    <div class='mt-4'>
                        <a href='/login' class='btn btn-primary'>Go to Login</a>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
    } else {
        die("
        <!DOCTYPE html>
        <html>
        <head>
            <title>Email Confirmation Failed</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
            <style>
                body { background-color: #f8f9fa; }
                .container { max-width: 600px; margin-top: 100px; }
                .alert { border-radius: 10px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='alert alert-danger text-center'>
                    <h2>Update Failed</h2>
                    <p>Failed to update email address. Please try again.</p>
                    <a href='/login' class='btn btn-primary'>Return to Login</a>
                </div>
            </div>
        </body>
        </html>
        ");
    }
}
?>