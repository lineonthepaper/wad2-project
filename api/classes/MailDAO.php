<?php
require_once __DIR__ . "/../common.php";

// DAO for accessing Address, Mail Item, Mail Status, Mail data in the DB.
class MailDAO
{
    private $conn;
    public function __construct(bool $server = true)
    {
        $conn = ConnectionManager::connect($server);
    }

    public function addMail(
        int $customerEmail,
        int $senderAddressId,
        int $recipientAddressId,
        array $mailItems,
        float $parcelLength,
        float $parcelWidth,
        float $parcelHeight,
        array $service
    ): bool {

        // tbd once db is set up
        return false;
    }

    public function getMailById(int $mailId): Mail
    {
        // tbd
        return new Mail(0, 0, 0, 0, [], 0, 0, 0, []);
    }

    public function getAllMailByCustomerEmail(string $customerEmail): array // of Mail
    {
        // tbd
        return [];
    }

    public function getAddressById(int $addressId): Address
    {
        // tbd
        return new Address(1, "", "", 0, 0, []);
    }

    public function getMailItemById(int $mailItemId): MailItem
    {
        // tbd
        return new MailItem(0, 0, "", "", 0, 0, 0, "");
    }

    public function getMailStatusesByMailId(int $mailid): array // of MailStatus
    {
        // tbd
        return [];
    }

    public function getMailStatusByStatusId(int $statusId): MailStatus
    {
        // tbd
        return new MailStatus(0, 0, 0, 0, "", "");
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
        // tbd
        return [];
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
        // tbd
        return [];
    }

    public function isMailPaid(int $mailId): bool
    {
        //tbd
        return false;
    }

    public function getMailTrackingNum(int $mailId): int
    {
        // if untracked, return 0
        // tbd
        return 0;
    }
}
