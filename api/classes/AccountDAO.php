<?php

require_once "common.php";

class AccountDAO
{
    public static function addAccount(Account $accountObj): void
    {
        $conn = ConnectionManager::connect();

        // tbd
    }

    public static function getAccountById(int $accountId): Account
    {
        $conn = ConnectionManager::connect();

        // tbd

        return new Account(0, "", "", "", false);
    }

    public static function updatePassword(int $accountId, string $newPasswordHashed): void
    {
        $conn = ConnectionManager::connect();

        // tbd
    }

    public static function updateDisplayName(int $accountId, string $newDisplayName): void
    {
        $conn = ConnectionManager::connect();

        // tbd
    }

    public static function updateEmail(int $accountId, string $newEmail): void
    {
        $conn = ConnectionManager::connect();

        // tbd
    }
}
