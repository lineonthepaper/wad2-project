<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . "/../bootstrap.php";

function generateRandomString($length = 10)
{
    return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
}

class AccountTest extends TestCase
{
    public function testAddAccount()
    {
        $accountDAO = new AccountDAO(false);
        $success = $accountDAO->addAccount(
            generateRandomString(),
            generateRandomString(5) . "@notarealemail.address",
            Account::hashPassword(generateRandomString()),
            true
        );
        $expectedResult = true;

        $this->assertEquals($expectedResult, $success);
    }

    public function testGetAccountById()
    {
        $accountDAO = new AccountDAO(false);
        $account = $accountDAO->getAccountById(11);

        $this->assertEquals("qHF9APZLwJ", $account->getDisplayName());
    }

    public function testGetAccountByEmail()
    {
        $accountDAO = new AccountDAO(false);
        $account = $accountDAO->getAccountByEmail("mBeRQ@notarealemail.address");

        $this->assertEquals("qHF9APZLwJ", $account->getDisplayName());
    }

    public function testUpdatePassword()
    {
        $accountDAO = new AccountDAO(false);
        $success = $accountDAO->updatePassword(11, Account::hashPassword(generateRandomString()));

        $this->assertEquals(true, $success);
    }

    public function testUpdateDisplayName()
    {
        $accountDAO = new AccountDAO(false);
        $success = $accountDAO->updateDisplayName(12, generateRandomString());

        $this->assertEquals(true, $success);
    }

    public function testUpdateEmail()
    {
        $accountDAO = new AccountDAO(false);
        $success = $accountDAO->updateEmail(12, generateRandomString(5) . "@notarealemail.address");

        $this->assertEquals(true, $success);
    }
}
