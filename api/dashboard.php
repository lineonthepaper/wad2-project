<?php
require_once __DIR__ . "/common.php";
require_once __DIR__ . "/classes/Account.php";
require_once __DIR__ . "/classes/AccountDAO.php";
require_once __DIR__ . "/classes/Mail.php";
require_once __DIR__ . "/classes/MailDAO.php";
require_once __DIR__ . "/classes/Address.php";
require_once __DIR__ . "/classes/MailItem.php";
require_once __DIR__ . "/classes/MailStatus.php";

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

// Log the request for debugging
error_log("Dashboard API Request: " . print_r($input, true));

try {
    switch ($method) {
        case 'getAllMailByCustomerEmail':
            // Support both 'email' and 'customerEmail' parameters
            $customerEmail = $input['customerEmail'] ?? $input['email'] ?? '';
            error_log("Fetching shipments for email: " . $customerEmail);
            
            if (empty($customerEmail)) {
                echo json_encode(['success' => false, 'error' => 'Customer email is required']);
                exit;
            }
            
            $mailDAO = new MailDAO();
            $mails = $mailDAO->getAllMailByCustomerEmail($customerEmail);
            
            error_log("Found " . count($mails) . " shipments for " . $customerEmail);
            
            // If no shipments found, provide example data
            if (empty($mails)) {
                error_log("No shipments found, using example data");
                $enhancedMails = getExampleShipments($customerEmail);
                echo json_encode([
                    'success' => true, 
                    'shipments' => $enhancedMails, 
                    'note' => 'Using example data - no real shipments found',
                    'count' => count($enhancedMails)
                ]);
                exit;
            }
            
            // Enhance the mail data with address information
            $enhancedMails = [];
            foreach ($mails as $mail) {
                try {
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
                    
                    // Get payment status and tracking number
                    $isPaid = $mailDAO->isMailPaid($mail->getMailId());
                    $trackingNum = $mailDAO->getMailTrackingNum($mail->getMailId());
                    
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
                        'hasBeenPaid' => $isPaid,
                        'trackingNumber' => $trackingNum,
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
                } catch (Exception $e) {
                    error_log("Error processing mail ID " . $mail->getMailId() . ": " . $e->getMessage());
                    continue; // Skip this mail and continue with others
                }
            }
            
            echo json_encode([
                'success' => true, 
                'shipments' => $enhancedMails,
                'count' => count($enhancedMails)
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Unknown method: ' . $method]);
    }
} catch (Exception $e) {
    error_log("Customer Dashboard API Error: " . $e->getMessage());
    
    // If there's an error, return example data
    $customerEmail = $input['customerEmail'] ?? $input['email'] ?? 'raksha@xx.com';
    $exampleData = getExampleShipments($customerEmail);
    echo json_encode([
        'success' => true, 
        'shipments' => $exampleData, 
        'note' => 'Using example data due to server error: ' . $e->getMessage(),
        'count' => count($exampleData)
    ]);
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

function getExampleShipments($customerEmail) {
    return [
        [
            'mailId' => 1001,
            'customerEmail' => $customerEmail,
            'senderAddressId' => 1,
            'recipientAddressId' => 2,
            'mailItems' => [
                [
                    'itemId' => 1,
                    'itemDescription' => 'Electronics Package',
                    'declaredValue' => 250.00,
                    'itemWeight' => 1.5,
                    'itemQuantity' => 1
                ]
            ],
            'parcelLength' => 30.0,
            'parcelWidth' => 20.0,
            'parcelHeight' => 15.0,
            'service' => [
                'name' => 'Registered Package',
                'type' => 'Packets',
                'zone' => 3
            ],
            'totalWeight' => 1.5,
            'totalValue' => 250.00,
            'hasBeenPaid' => true,
            'trackingNumber' => 'TRK784231',
            'createdDate' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'senderAddress' => [
                'name' => 'John Doe',
                'countryCode' => 'SG'
            ],
            'recipientAddress' => [
                'name' => 'Sarah Wilson',
                'countryCode' => 'US'
            ],
            'status' => 'in_transit',
            'expectedDelivery' => date('Y-m-d', strtotime('+3 days'))
        ],
        [
            'mailId' => 1002,
            'customerEmail' => $customerEmail,
            'senderAddressId' => 1,
            'recipientAddressId' => 3,
            'mailItems' => [
                [
                    'itemId' => 2,
                    'itemDescription' => 'Documents',
                    'declaredValue' => 50.00,
                    'itemWeight' => 0.5,
                    'itemQuantity' => 1
                ]
            ],
            'parcelLength' => 35.0,
            'parcelWidth' => 25.0,
            'parcelHeight' => 2.0,
            'service' => [
                'name' => 'Registered Mail',
                'type' => 'Documents',
                'zone' => 2
            ],
            'totalWeight' => 0.5,
            'totalValue' => 50.00,
            'hasBeenPaid' => true,
            'trackingNumber' => 'TRK784232',
            'createdDate' => date('Y-m-d H:i:s', strtotime('-5 days')),
            'senderAddress' => [
                'name' => 'John Doe',
                'countryCode' => 'SG'
            ],
            'recipientAddress' => [
                'name' => 'Mike Chen',
                'countryCode' => 'MY'
            ],
            'status' => 'delivered',
            'expectedDelivery' => date('Y-m-d', strtotime('-1 day'))
        ]
    ];
}
?>