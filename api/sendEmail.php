<?php
header("Content-Type: application/json");

require_once __DIR__ . "/bootstrap.php";

use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\EmailParams;
use MailerSend\Helpers\Builder\Recipient;

$mailerSend = new MailerSend(["api_key" => $_ENV["MAILERSEND_KEY"]]);

echo json_encode(["key" => $_ENV["MAILERSEND_KEY"]]);

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "POST") {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) $payload = [];

    $method = $payload['method'] ?? '';

    try {
        $recipients = [];
        foreach ($payload["to"] as $recipient) {
            $recipients[] = new Recipient($recipient["email"], $recipient["name"]);
        }
        $email = (new EmailParams())
            ->setFrom("fluffyshipping@test-z0vklo6p0k7l7qrx.mlsender.net")
            ->setFromName($payload["from"]["name"])
            ->setRecipients($recipients)
            ->setSubject($payload["subject"])
            ->setHtml($payload["html"])
            ->setText($payload["text"]);

        $mailerSend->email->send($email);
    } catch (Exception $e) {
        echo json_encode(["message" => "Caught exception " . $e->getMessage()]);
        exit;
    }
}
