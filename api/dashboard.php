<?php
require_once __DIR__ . "/common.php";
require_once __DIR__ . "/classes/Account.php";
require_once __DIR__ . "/classes/AccountDAO.php";
require_once __DIR__ . "/classes/Mail.php";
require_once __DIR__ . "/classes/MailDAO.php";
require_once __DIR__ . "/classes/Address.php";

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$method = $input['method'] ?? '';

try {
    switch ($method) {
        case 'getAllMailByCustomerEmail':
            // Support both 'email' and 'customerEmail' parameters
            $customerEmail = $input['customerEmail'] ?? $input['email'] ?? '';
            if (empty($customerEmail)) {
                echo json_encode(['success' => false, 'error' => 'Customer email is required']);
                exit;
            }
            
            $mailDAO = new MailDAO();
            $mails = $mailDAO->getAllMailByCustomerEmail($customerEmail);
            
            // Enhance the mail data with address information
            $enhancedMails = [];
            foreach ($mails as $mail) {
                // Get addresses for coordinates
                $senderAddress = $mailDAO->getAddressById($mail->getSenderAddressId());
                $recipientAddress = $mailDAO->getAddressById($mail->getRecipientAddressId());
                
                // Get latest status
                $statuses = $mailDAO->getMailStatusesByMailId($mail->getMailId());
                $latestStatus = !empty($statuses) ? end($statuses) : null;
                
                // Calculate total value from mail items
                $totalValue = 0;
                foreach ($mail->getMailItems() as $item) {
                    $totalValue += $item->getDeclaredValue();
                }
                
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
                    'service' => $mail->getService(),
                    'totalWeight' => $mail->getTotalWeight(),
                    'totalValue' => $totalValue,
                    'hasBeenPaid' => $mail->getHasBeenPaid(),
                    'trackingNumber' => $mail->getTrackingNumber(),
                    'createdDate' => date('Y-m-d H:i:s'), // Use current date as fallback
                    'senderAddress' => $senderAddress ? [
                        'name' => $senderAddress->getName(),
                        'countryCode' => $senderAddress->getAddress()['countryCode']
                    ] : null,
                    'recipientAddress' => $recipientAddress ? [
                        'name' => $recipientAddress->getName(),
                        'countryCode' => $recipientAddress->getAddress()['countryCode']
                    ] : null,
                    'status' => determineStatus($latestStatus),
                    'expectedDelivery' => calculateExpectedDelivery($mail->getService())
                ];
                $enhancedMails[] = $enhancedMail;
            }
            
            echo json_encode(['success' => true, 'shipments' => $enhancedMails]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Unknown method']);
    }
} catch (Exception $e) {
    error_log("Customer Dashboard API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Internal server error: ' . $e->getMessage()]);
}

// Helper functions
function determineStatus($latestStatus) {
    if (!$latestStatus) return 'pending';
    
    $statusCode = $latestStatus->getStatusCode();
    
    // Map your status codes to dashboard statuses
    $statusMap = [
        1 => 'pending',      // Created
        2 => 'in_transit',   // In Transit
        3 => 'in_transit',   // Processed
        4 => 'delivered',    // Delivered
        5 => 'pending'       // Awaiting Pickup
    ];
    
    return $statusMap[$statusCode] ?? 'pending';
}

function calculateExpectedDelivery($service) {
    $minDays = 5; // Default minimum days
    // Calculate expected delivery based on service type
    if (isset($service['name'])) {
        $serviceName = $service['name'];
        if (strpos($serviceName, 'Registered') !== false) {
            $minDays = 3;
        } elseif (strpos($serviceName, 'Basic') !== false) {
            $minDays = 7;
        } elseif (strpos($serviceName, 'Standard') !== false) {
            $minDays = 2;
        }
    }
    
    $expectedDate = new DateTime();
    $expectedDate->modify("+$minDays days");
    return $expectedDate->format('Y-m-d');
}
?>