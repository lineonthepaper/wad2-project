<?php
require_once "common.php";

// DAO for accessing Address, Mail Item, Mail Status, Mail data in the DB.
class MailDAO
{
    public static function insertMail(Mail $mailObj): void
    {
        $conn = ConnectionManager::connect();

        // tbd once db is set up
    }

    public static function getMailById(int $mailId): Mail
    {
        $conn = ConnectionManager::connect();
        // tbd
        return new Mail(0, 0, 0, 0, [], 0, 0, 0, []);
    }

    public static function getAllMailByCustomerEmail(string $customerEmail): array // of Mail
    {
        $conn = ConnectionManager::connect();
        // tbd
        return [];
    }

    public static function getAddressById(int $addressId): Address
    {
        $conn = ConnectionManager::connect();
        // tbd
        return new Address(1, "", "", 0, 0, []);
    }

    public static function getMailItemById(int $mailItemId): MailItem
    {
        $conn = ConnectionManager::connect();
        // tbd
        return new MailItem(0, 0, "", "", 0, 0, 0, "");
    }

    public static function getMailStatusesByMailId(int $mailid): array // of MailStatus
    {
        $conn = ConnectionManager::connect();
        // tbd
        return [];
    }

    public static function getMailStatusByStatusId(int $statusId): MailStatus
    {
        $conn = ConnectionManager::connect();
        // tbd
        return new MailStatus(0, 0, 0, 0, "", "");
    }

    public static function getServiceRate(array $service): array
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
        $conn = ConnectionManager::connect();
        // tbd
        return [];
    }

    public static function getServiceTime(array $service): array
    {
        /*
        Should return array $period:
        [
        "min" => $minDays,
        "max" => $maxDays
        ]
        */
        $conn = ConnectionManager::connect();
        // tbd
        return [];
    }

    public static function isMailPaid(int $mailId): bool
    {
        $conn = ConnectionManager::connect();
        //tbd
        return false;
    }

    public static function getMailTrackingNum(int $mailId): int
    {
        // if untracked, return 0
        // tbd
        return 0;
    }
}
