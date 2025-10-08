<?php

require_once __DIR__ . "/common.php";

header("Content-Type: application/json");

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "POST") {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) $payload = [];

    $method = $payload['method'] ?? '';

    if ($method == "addMail") {
        try {
            $mailDAO = new MailDAO();
            $mailItems = [];
            foreach ($payload["mailItems"] as $mailItem) {
                $mailItems[] = new MailItem(
                    null,
                    null,
                    $mailItem["itemDescription"],
                    $mailItem["declaredCurrency"],
                    $mailItem["declaredValue"],
                    $mailItem["itemWeight"],
                    $mailItem["itemQuantity"],
                    $mailItem["hsCode"]
                );
            }

            $success = $mailDAO->addMail(
                new Mail(
                    null,
                    $payload["customerEmail"],
                    $payload["senderAddressId"],
                    $payload["recipientAddressId"],
                    $mailItems,
                    $payload["parcelLength"],
                    $payload["parcelWidth"],
                    $payload["parcelHeight"],
                    $payload["service"]
                )
            );
            if ($success) {
                echo json_encode(
                    ["message" => "Mail created successfully."]
                );
                exit;
            } else {
                echo json_encode(["message" => "Error in mail creation."]);
                exit;
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Caught exception " . $e->getMessage()]);
            exit;
        }
    } elseif ($method == "addMailStatus") {
        try {
            $mailDAO = new MailDAO();
            $success = $mailDAO->addMailStatus(
                new MailStatus(
                    null,
                    $payload["mailId"],
                    $payload["statusCode"],
                    $payload["statusTimestamp"],
                    $payload["statusDescription"],
                    $payload["statusLocation"]
                )
            );

            if ($success) {
                echo json_encode(
                    ["message" => "Mail status created successfully."]
                );
                exit;
            } else {
                echo json_encode(["message" => "Error in mail status creation."]);
                exit;
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Caught exception " . $e->getMessage()]);
            exit;
        }
    } elseif ($method == "getMailById") {
        try {
            $mailDAO = new MailDAO();
            $mail = $mailDAO->getMailById($payload["mailId"]);
            if ($mail) {
                echo json_encode([
                    "message" => "Found mail.",
                    "mail" => $mail->jsonSerialize()
                ]);
                exit;
            } else {
                echo json_encode(["message" => "Mail could not be found."]);
                exit;
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Caught exception " . $e->getMessage()]);
            exit;
        }
    } elseif ($method == "getAllMailByCustomerEmail") {
        try {
            $mailDAO = new MailDAO();
            $mailArr = $mailDAO->getAllMailByCustomerEmail($payload["customerEmail"]);

            if ($mailArr) {
                echo json_encode([
                    "message" => "Found mail.",
                    "mail" => $mailArr
                ]);
                exit;
            } else {
                echo json_encode(["message" => "Mail could not be found."]);
                exit;
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Caught exception " . $e->getMessage()]);
            exit;
        }
    } elseif ($method == "getMailItemByItemId") {
        try {
            $mailDAO = new MailDAO();
            $mailItem = $mailDAO->getMailItemByItemId($payload["itemId"]);
            if ($mailItem) {
                echo json_encode(["message" => "Found mail item.", "mailItem" => $mailItem->jsonSerialize()]);
                exit;
            } else {
                echo json_encode(["message" => "Mail item could not be found."]);
                exit;
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Caught exception " . $e->getMessage()]);
            exit;
        }
    } elseif ($method == "getMailStatusesByMailId") {
        try {
            $mailDAO = new MailDAO();
            $mailStatusArr = $mailDAO->getMailStatusesByMailId($payload["mailId"]);

            if ($mailStatusArr) {
                echo json_encode([
                    "message" => "Found mail statuses.",
                    "mailStatuses" => $mailStatusArr
                ]);
                exit;
            } else {
                echo json_encode(["message" => "Mail statuses could not be found."]);
                exit;
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Caught exception " . $e->getMessage()]);
            exit;
        }
    } elseif ($method == "getMailStatusByStatusId") {
        try {
            $mailDAO = new MailDAO();
            $mailStatus = $mailDAO->getMailStatusByStatusId($payload["statusId"]);
            if ($mailStatus) {
                echo json_encode(["message" => "Found mail status.", "mailStatus" => $mailStatus->jsonSerialize()]);
                exit;
            } else {
                echo json_encode(["message" => "Mail status could not be found."]);
                exit;
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Caught exception " . $e->getMessage()]);
            exit;
        }
    } elseif ($method == "getServiceRate") {
        try {
            $mailDAO = new MailDAO();
            $serviceRate = $mailDAO->getServiceRate(
                [
                    $payload["name"],
                    $payload["type"],
                    $payload["zone"]
                ]
            );
            if ($serviceRate) {
                echo json_encode(["message" => "Found service rate.", "serviceRate" => $serviceRate]);
                exit;
            } else {
                echo json_encode(["message" => "Service rate could not be found."]);
                exit;
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Caught exception " . $e->getMessage()]);
            exit;
        }
    } elseif ($method == "getServiceTime") {
        try {
            $mailDAO = new MailDAO();
            $serviceTime = $mailDAO->getServiceTime(
                [
                    $payload["name"],
                    $payload["type"],
                    $payload["zone"]
                ]
            );
            if ($serviceTime) {
                echo json_encode(["message" => "Found service time.", "serviceTime" => $serviceTime]);
                exit;
            } else {
                echo json_encode(["message" => "Service time could not be found."]);
                exit;
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Caught exception " . $e->getMessage()]);
            exit;
        }
    } elseif ($method == "isMailPaid") {
        try {
            $mailDAO = new MailDAO();
            $isMailPaid = $mailDAO->isMailPaid($payload["mailId"]);

            echo json_encode(["isMailPaid" => $isMailPaid]);
            exit;
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Caught exception " . $e->getMessage()]);
            exit;
        }
    } elseif ($method == "getMailTrackingNum") {
        try {
            $mailDAO = new MailDAO();
            $trackingNum = $mailDAO->getMailTrackingNum($payload["mailId"]);

            echo json_encode(["trackingNum" => $trackingNum]);
            exit;
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Caught exception " . $e->getMessage()]);
            exit;
        }
    } elseif ($method == "getMatchingServices") {
        try {
            $mailDAO = new MailDAO();
            $services = $mailDAO->getMatchingServices(
                $payload["zone"],
                $payload["type"],
                $payload["weight"],
                $payload["height"],
                $payload["width"],
                $payload["length"]
            );

            echo json_encode(["services" => $services]);
            exit;
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Caught exception " . $e->getMessage()]);
            exit;
        }
    } elseif ($method == "getServiceInfo") {
        try {
            $mailDAO = new MailDAO();
            $info = $mailDAO->getServiceInfo(
                $payload["name"],
                $payload["type"]
            );

            if ($info) {
                echo json_encode(["message" => "Found service info.", "info" => $info]);
                exit;
            } else {
                echo json_encode(["message" => "Service info could not be found."]);
                exit;
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Caught exception " . $e->getMessage()]);
            exit;
        }
    } elseif ($method == "getZone") {
        try {
            $mailDAO = new MailDAO();
            $zone = $mailDAO->getZone(
                $payload["countryCode"]
            );

            if ($zone) {
                echo json_encode(["message" => "Found zone.", "zone" => $zone]);
                exit;
            } else {
                echo json_encode(["message" => "Zone could not be found."]);
                exit;
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Caught exception " . $e->getMessage()]);
            exit;
        }
    } elseif ($method == "getCountryCode") {
        try {
            $mailDAO = new MailDAO();
            $code = $mailDAO->getCountryCode(
                $payload["countryName"]
            );

            if ($code) {
                echo json_encode(["message" => "Found country code.", "countryCode" => $zone]);
                exit;
            } else {
                echo json_encode(["message" => "Country code could not be found."]);
                exit;
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Caught exception " . $e->getMessage()]);
            exit;
        }
    }
}
