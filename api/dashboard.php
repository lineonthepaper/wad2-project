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


error_log("Dashboard API Request: " . print_r($input, true));

try {
    switch ($method) {
        case 'getAllMailByCustomerEmail':
       
            $customerEmail = $input['customerEmail'] ?? $input['email'] ?? '';
            error_log("Fetching shipments for email: " . $customerEmail);
            
            if (empty($customerEmail)) {
                echo json_encode(['success' => false, 'error' => 'Customer email is required']);
                exit;
            }
            
            $mailDAO = new MailDAO();
            $mails = $mailDAO->getAllMailByCustomerEmail($customerEmail);
            
            error_log("Found " . count($mails) . " shipments for " . $customerEmail);
            
     
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
            
           
            $enhancedMails = [];
            foreach ($mails as $mail) {
                try {
             
                    $senderAddress = $mailDAO->getAddressById($mail->getSenderAddressId());
                    $recipientAddress = $mailDAO->getAddressById($mail->getRecipientAddressId());
                    
    
                    $latestStatus = null;
                    try {
                        $statuses = $mailDAO->getMailStatusesByMailId($mail->getMailId());
                        $latestStatus = !empty($statuses) ? end($statuses) : null;
                    } catch (Exception $e) {
                        error_log("Error getting status for mail " . $mail->getMailId() . ": " . $e->getMessage());
                        $latestStatus = null;
                    }
                    
        
                    $totalValue = 0;
                    foreach ($mail->getMailItems() as $item) {
                        $totalValue += $item->getDeclaredValue();
                    }
                    
         
                    $isPaid = $mailDAO->isMailPaid($mail->getMailId());
                    $trackingNum = $mailDAO->getMailTrackingNum($mail->getMailId());
                    
       
                    $senderCoords = getCoordinatesForAddress($senderAddress);
                    $recipientCoords = getCoordinatesForAddress($recipientAddress);
                    
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
                            'countryCode' => $senderAddress->getAddress()['countryCode'],
                            'coordinates' => $senderCoords
                        ] : null,
                        'recipientAddress' => $recipientAddress ? [
                            'name' => $recipientAddress->getName(),
                            'countryCode' => $recipientAddress->getAddress()['countryCode'],
                            'coordinates' => $recipientCoords
                        ] : null,
                        'status' => determineStatus($latestStatus),
                        'expectedDelivery' => calculateExpectedDelivery($mail->getService())
                    ];
                    $enhancedMails[] = $enhancedMail;
                } catch (Exception $e) {
                    error_log("Error processing mail ID " . $mail->getMailId() . ": " . $e->getMessage());
                    continue; 
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
    
    
    $customerEmail = $input['customerEmail'] ?? $input['email'] ?? 'raksha@xx.com';
    $exampleData = getExampleShipments($customerEmail);
    echo json_encode([
        'success' => true, 
        'shipments' => $exampleData, 
        'note' => 'Using example data due to server error: ' . $e->getMessage(),
        'count' => count($exampleData)
    ]);
}


function determineStatus($latestStatus) {
    if (!$latestStatus) return 'pending';
    
    $statusCode = $latestStatus->getStatusCode();
    
    
    $statusMap = [
        1 => 'pending',      
        2 => 'in_transit',   
        3 => 'in_transit',   
        4 => 'delivered',    
        5 => 'pending'       
    ];
    
    return $statusMap[$statusCode] ?? 'pending';
}

function calculateExpectedDelivery($service) {
    $minDays = 5; 
    
    
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

function getCoordinatesForAddress($address) {
    if (!$address) {
        return ['lat' => 1.28478, 'lng' => 103.776222]; // Default to Singapore
    }
    
    $addressData = $address->getAddress();
    $countryCode = $addressData['countryCode'] ?? 'SG';
    
    // Load country data from JSON file
    $countryDataFile = __DIR__ . '/../json/countryData.json';
    $countryCoordinates = [];
    
    if (file_exists($countryDataFile)) {
        $countryData = json_decode(file_get_contents($countryDataFile), true);
        foreach ($countryData as $country) {
            $countryCoordinates[$country['code2']] = [
                'lat' => $country['lat'],
                'lng' => $country['long'] // Note: your JSON uses 'long' instead of 'lng'
            ];
        }
    } else {
        // Fallback to hardcoded data if JSON file doesn't exist
        error_log("Country data file not found: " . $countryDataFile);
        $countryCoordinates = [
            'SG' => ['lat' => 1.28478, 'lng' => 103.776222],
            'MY' => ['lat' => 3.153398, 'lng' => 101.697097],
            'US' => ['lat' => 38.883757, 'lng' => -77.025347],
            'UK' => ['lat' => 51.525751, 'lng' => -0.111290],
            'CN' => ['lat' => 39.915494, 'lng' => 116.359857],
            'JP' => ['lat' => 35.687015, 'lng' => 139.764585],
            'KR' => ['lat' => 37.561637, 'lng' => 126.982072],
            'AU' => ['lat' => -37.825337, 'lng' => 144.999122],
            'CA' => ['lat' => 45.381872, 'lng' => -75.690368],
            'FR' => ['lat' => 48.831429, 'lng' => 2.276772],
            'DE' => ['lat' => 50.737742, 'lng' => 7.098077],
            'IT' => ['lat' => 41.830555, 'lng' => 12.467820],
            'IN' => ['lat' => 28.622656, 'lng' => 77.213115],
            'TH' => ['lat' => 13.889791, 'lng' => 100.569561],
            'VN' => ['lat' => 21.028398, 'lng' => 105.833947],
            'PH' => ['lat' => 14.595453, 'lng' => 120.979232],
            'ID' => ['lat' => 1.153575, 'lng' => 104.004398]
        ];
    }
    
    return $countryCoordinates[$countryCode] ?? ['lat' => 1.28478, 'lng' => 103.776222];
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
                'countryCode' => 'SG',
                'coordinates' => ['lat' => 1.28478, 'lng' => 103.776222]
            ],
            'recipientAddress' => [
                'name' => 'Sarah Wilson',
                'countryCode' => 'US',
                'coordinates' => ['lat' => 38.883757, 'lng' => -77.025347]
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
                'countryCode' => 'SG',
                'coordinates' => ['lat' => 1.28478, 'lng' => 103.776222]
            ],
            'recipientAddress' => [
                'name' => 'Mike Chen',
                'countryCode' => 'MY',
                'coordinates' => ['lat' => 3.153398, 'lng' => 101.697097]
            ],
            'status' => 'delivered',
            'expectedDelivery' => date('Y-m-d', strtotime('-1 day'))
        ]
    ];
}
?>