<?php
require_once __DIR__ . "/../common.php";

// DAO for accessing Address, Mail Item, Mail Status, Mail data in the DB.
class MailDAO
{
    private $conn;
    public function __construct(bool $server = true)
    {
        $this->conn = ConnectionManager::connect($server);
    }

    public function addAddress(
        Address $newAddress
    ): bool {
        $query = "insert into 
        address(name, email, phone, phone_country_code, 
        address_line_1, address_line_2, address_line_3, postal_code, country_code)
        values($1, $2, $3, $4, $5, $6, $7, $8, $9)";

        $params = [
            $newAddress->getName(),
            $newAddress->getEmail(),
            $newAddress->getPhone(),
            $newAddress->getPhoneCountryCode(),
            $newAddress->getAddress()["addressLines"][0],
            $newAddress->getAddress()["addressLines"][1],
            $newAddress->getAddress()["addressLines"][2],
            $newAddress->getAddress()["postalCode"],
            $newAddress->getAddress()["countryCode"]
        ];

        $success = pg_query_params($this->conn, $query, $params);

        return $success !== false;
    }

    public function addMail(
        Mail $newMail
    ): bool {

        $query = "insert into 
        mail(customer_email, sender_address_id, recipient_address_id, parcel_length, parcel_width, parcel_height,
        has_been_paid, tracking_number, service_name, service_type, service_zone) 
        values($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11)";
        $params = [
            $newMail->getCustomerEmail(),
            $newMail->getSenderAddressId(),
            $newMail->getRecipientAddressId(),
            $newMail->getParcelLength(),
            $newMail->getParcelWidth(),
            $newMail->getParcelHeight(),
            0,
            0,
            $newMail->getService()["name"],
            $newMail->getService()["type"],
            $newMail->getService()["zone"]
        ];
        $result = pg_query_params($this->conn, $query, $params);
        if ($result === false) {
            return false;
        }

        $newMailId = pg_fetch_array($result, null, PGSQL_ASSOC)["mail_id"];

        foreach ($newMail->getMailItems() as $mailItem) {
            $query2 = "insert into 
            mail(mail_id, item_description, declared_currency, declared_value, item_weight, item_quantity, hs_code)
            values($1, $2, $3, $4, $5, $6, $7)";
            $params2 = [
                $newMailId,
                $mailItem->getItemDescription(),
                $mailItem->getDeclaredCurrency(),
                $mailItem->getDeclaredValue(),
                $mailItem->getItemWeight(),
                $mailItem->getItemQuantity(),
                $mailItem->getHsCode()
            ];
            $result2 = pg_query_params($this->conn, $query2, $params2);
            if ($result2 === false) {
                return false;
            }
        }
        return true;
    }

    public function addMailStatus(MailStatus $newMailStatus): bool
    {
        $query = "insert into 
        mail_status(mail_id, status_code, status_time_stamp, status_description, status_location)
        values($1, $2, $3, $4, $5)";

        $params = [
            $newMailStatus->getMailId(),
            $newMailStatus->getStatusCode(),
            (new DateTime("@" . time()))->format(DateTime::ATOM),
            $newMailStatus->getStatusDescription(),
            $newMailStatus->getStatusLocation()
        ];

        $success = pg_query_params($this->conn, $query, $params);
        return $success !== false;
    }

    public function getMailById(int $mailId): ?Mail
    {
        $query = "select * from mail where mail_id = $1";
        $params = [$mailId];

        $res = pg_fetch_array(pg_query_params($this->conn, $query, $params), 0, PGSQL_ASSOC);

        if ($res === false) {
            return null;
        }

        $mailItems = [];

        $query2 = "select * from mail_item where mail_id = $1";
        $res2 = pg_fetch_all(pg_query_params($this->conn, $query2, $params));

        foreach ($res2 as $mI) {
            $mailItems[] = new MailItem(
                $mI["item_id"],
                $mailId,
                $mI["item_description"],
                $mI["declared_currency"],
                $mI["declared_value"],
                $mI["item_weight"],
                $mI["item_quantity"],
                $mI["hs_code"]
            );
        }

        return new Mail(
            $mailId,
            $res["customer_email"],
            $res["sender_address_id"],
            $res["recipient_address_id"],
            $mailItems,
            $res["parcel_length"],
            $res["parcel_width"],
            $res["parcel_height"],
            [
                "name" => $res["service_name"],
                "type" => $res["service_type"],
                "zone" => $res["service_zone"]
            ]
        );
    }

    public function getAllMailByCustomerEmail(string $customerEmail): array // of Mail
    {
        $mail = [];

        $query = "select * from mail where customer_email = $1";
        $params = [$customerEmail];

        $res = pg_fetch_all(pg_query_params($this->conn, $query, $params));
        if ($res === false) {
            return [];
        }

        foreach ($res as $m) {
            $mailId = $res["mail_id"];
            $query2 = "select * from mail_item where mail_id = $1";
            $params2 = [$mailId];

            $res2 = pg_fetch_all(pg_query_params($this->conn, $query2, $params2));

            $mailItems = [];
            if ($res2 !== false) {
                foreach ($res2 as $mI) {
                    $mailItems[] = new MailItem(
                        $mI["item_id"],
                        $mailId,
                        $mI["item_description"],
                        $mI["declared_currency"],
                        $mI["declared_value"],
                        $mI["item_weight"],
                        $mI["item_quantity"],
                        $mI["hs_code"]
                    );
                }
            }
            $mail[] = new Mail(
                $mailId,
                $res["customer_email"],
                $res["sender_address_id"],
                $res["recipient_address_id"],
                $mailItems,
                $res["parcel_length"],
                $res["parcel_width"],
                $res["parcel_height"],
                [
                    "name" => $res["service_name"],
                    "type" => $res["service_type"],
                    "zone" => $res["service_zone"]
                ]
            );
        }

        return $mail;
    }

    public function getAddressById(int $addressId): ?Address
    {
        $query = "select * from address where address_id = $1";
        $params = [$addressId];

        $res = pg_fetch_array(pg_query_params($this->conn, $query, $params), 0, PGSQL_ASSOC);

        if ($res == false) {
            return null;
        }

        return new Address(
            (int)$res["address_id"],
            $res["name"],
            $res["email"],
            $res["phone"],
            $res["phone_country_code"],
            [
                "addressLines" => [
                    $res["address_line_1"],
                    $res["address_line_2"],
                    $res["address_line_3"]
                ],
                "postalCode" => $res["postal_code"],
                "countryCode" => $res["country_code"]
            ]
        );
    }

    public function getMailItemByItemId(int $itemId): ?MailItem
    {
        $query = "select * from mail_item where item_id = $1";
        $params = [$itemId];

        $res = pg_fetch_array(pg_query_params($this->conn, $query, $params), 0, PGSQL_ASSOC);

        if ($res === false) {
            return null;
        }

        return new MailItem(
            $itemId,
            $res["mail_id"],
            $res["item_description"],
            $res["declared_currency"],
            $res["declared_value"],
            $res["item_weight"],
            $res["item_quantity"],
            $res["hs_code"]
        );
    }

    public function getMailStatusesByMailId(int $mailId): array // of MailStatus
    {
        $mailStatus = [];
        $query = "select * from mail_status where mail_id = $1";
        $params = [$mailId];

        $res = pg_fetch_all(pg_query_params($this->conn, $query, $params));

        if ($res === false) {
            return [];
        }

        foreach ($res as $mS) {
            $mailStatus[] = new MailStatus(
                $mS["status_id"],
                $mailId,
                $mS["status_code"],
                $mS["status_time_stamp"],
                $mS["status_description"],
                $mS["status_location"]
            );
        }
        return $mS;
    }

    public function getMailStatusByStatusId(int $statusId): ?MailStatus
    {
        // tbd
        $query = "select * from mail_status where status_id = $1";
        $params = [$statusId];

        $res = pg_fetch_array(pg_query_params($this->conn, $query, $params), 0, PGSQL_ASSOC);

        if ($res === false) {
            return null;
        }

        return new MailStatus(
            $statusId,
            $res["mail_id"],
            $res["status_code"],
            $res["status_time_stamp"],
            $res["status_description"],
            $res["status_location"]
        );
    }

    public function getServiceRate(array $service): array
    {
        /*
        Should return array $rates:
        [
        "baseRate" => $baseRate, 
        "addRate" => $addRate,
        "baseWeight" => $baseWeight,
        "addWeight" => $addWeight
        ]
        */

        $query = "select service_base_cost, service_add_cost, service_base_weight, service_add_weight 
        from service_cost 
        where service_name = $1, service_type = $2, service_zone = $3";
        $params = [
            $service["name"],
            $service["type"],
            $service["zone"]
        ];

        $res = pg_fetch_array(pg_query_params($this->conn, $query, $params), 0, PGSQL_ASSOC);

        if ($res === false) {
            return [];
        }

        return [
            "baseRate" => $res["service_base_cost"],
            "addRate" => $res["service_add_cost"],
            "baseWeight" => $res["service_base_weight"],
            "addWeight" => $res["service_add_weight"]
        ];
    }

    public function getServiceTime(array $service): array
    {
        /*
        Should return array $period:
        [
        "min" => $minDays,
        "max" => $maxDays
        ]
        */

        $query = "select service_minimum_days, service_maximum_days 
        from service_cost 
        where service_name = $1, service_type = $2, service_zone = $3";
        $params = [
            $service["name"],
            $service["type"],
            $service["zone"]
        ];

        $res = pg_fetch_array(pg_query_params($this->conn, $query, $params), 0, PGSQL_ASSOC);

        if ($res === false) {
            return [];
        }

        return [
            "min" => $res["service_minimum_days"],
            "max" => $res["service_maximum_days"]
        ];
    }

    public function isMailPaid(int $mailId): bool
    {

        $query = "select has_been_paid from mail where mail_id = $1";
        $params = [$mailId];

        $res = pg_fetch_result(pg_query_params($this->conn, $query, $params), 0, 0) === "t" ? true : false;

        return $res;
    }

    public function getMailTrackingNum(int $mailId): int
    {
        // if untracked, return 0

        $query = "select tracking_number from mail where mail_id = $1";
        $params = [$mailId];

        $res = pg_fetch_result(pg_query_params($this->conn, $query, $params), 0, 0);

        if ($res === false) {
            return 0;
        }

        return $res;
    }
}
