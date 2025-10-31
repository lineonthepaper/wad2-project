<?php
require_once __DIR__ . "/../common.php";
require_once __DIR__ . "/../model/Account.php";
require_once __DIR__ . "/../model/AccountDAO.php";
require_once __DIR__ . "/../model/Mail.php";
require_once __DIR__ . "/../model/MailDAO.php";
require_once __DIR__ . "/../model/Address.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$method = $input['method'] ?? '';

try {
    switch ($method) {
        case 'getAllMailByCustomerEmail':
            $customerEmail = $input['customerEmail'] ?? '';
            if (empty($customerEmail)) {
                echo json_encode(['success' => false, 'error' => 'Customer email is required']);
                exit;
            }
            
            $mailDAO = new MailDAO();
            $mails = $mailDAO->getAllMailByCustomerEmail($customerEmail);
            
            // Enhance the mail data with address information
            $enhancedMails = [];
            foreach ($mails as $mail) {
                $enhancedMail = [
                    'mailId' => $mail->getMailId(),
                    'customerEmail' => $mail->getCustomerEmail(),
                    'senderAddressId' => $mail->getSenderAddressId(),
                    'recipientAddressId' => $mail->getRecipientAddressId(),
                    'mailItems' => array_map(function($item) {
                        return [
                            'itemId' => $item->getItemId(),
                            'itemDescription' => $item->getItemDescription(),
                            'declaredValue' => $item->getDeclaredValue(),
                            'itemWeight' => $item->getItemWeight(),
                            'itemQuantity' => $item->getItemQuantity()
                        ];
                    }, $mail->getMailItems()),
                    'parcelLength' => $mail->getParcelLength(),
                    'parcelWidth' => $mail->getParcelWidth(),
                    'parcelHeight' => $mail->getParcelHeight(),
                    'serviceName' => $mail->getServiceName(),
                    'serviceType' => $mail->getServiceType(),
                    'totalWeight' => $mail->getTotalWeight(),
                    'totalValue' => $mail->getTotalValue(),
                    'hasBeenPaid' => $mail->getHasBeenPaid(),
                    'trackingNumber' => $mail->getTrackingNumber(),
                    'createdDate' => $mail->getCreatedDate(),
                    'senderCountry' => 'SG', // Default, you might want to get from address
                    'recipientCountry' => 'US' // Default, you might want to get from address
                ];
                $enhancedMails[] = $enhancedMail;
            }
            
            echo json_encode(['success' => true, 'mails' => $enhancedMails]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Unknown method']);
    }
} catch (Exception $e) {
    error_log("Customer Dashboard API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
?>