<?php
header("Content-Type: application/json");

require_once __DIR__ . "/bootstrap.php";

use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\EmailParams;
use MailerSend\Helpers\Builder\Personalization;
use MailerSend\Helpers\Builder\Recipient;

$mailerSend = new MailerSend(["api_key" => $_ENV["MAILERSEND_KEY"]]);

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "POST") {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) $payload = [];

    try {
        $recipients = [];
        $personalization = [];
        foreach ($payload["to"] as $recipient) {
            $recipients[] = new Recipient($recipient["email"], $recipient["name"]);
            $personalization[] = new Personalization($recipient["email"], [
                "mail_id" => $payload["mailId"],
                "shipment_type" => $payload["shipmentType"],
                "support_email" => "singpostproj@gmail.com"
            ]);
        }
        $email = (new EmailParams())
            ->setFrom("fluffyshipping@test-z0vklo6p0k7l7qrx.mlsender.net")
            ->setFromName($payload["from"]["name"])
            ->setRecipients($recipients)
            ->setSubject($payload["subject"])
            ->setTemplateId('pxkjn41pe854z781')
            ->setPersonalization($personalization);

        $mailerSend->email->send($email);
    } catch (Exception $e) {
        echo json_encode(["message" => "Caught exception " . $e->getMessage()]);
        exit;
    }
}
