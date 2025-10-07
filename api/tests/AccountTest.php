<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . "/../bootstrap.php";

require_once "testUtil.php";

class AccountTest extends TestCase
{
    public function testAddAccount()
    {
        $accountDAO = new AccountDAO(false);
        $success = $accountDAO->addAccount(
            new Account(
                null,
                generateRandomString(),
                generateRandomString(5) . "@notarealemail.address",
                Account::hashPassword(generateRandomString()),
                true
            )
        );
        $expectedResult = true;

        $this->assertEquals($expectedResult, $success);
    }

    public function testGetAccountById()
    {
        $accountDAO = new AccountDAO(false);
        $account = $accountDAO->getAccountById(1);

        $this->assertEquals("muh hee ow", $account->getDisplayName());
    }

    public function testGetAccountByEmail()
    {
        $accountDAO = new AccountDAO(false);
        $account = $accountDAO->getAccountByEmail("muhheeow@fakeemail.com");

        $this->assertEquals("muh hee ow", $account->getDisplayName());
    }

    public function testUpdatePassword()
    {
        $accountDAO = new AccountDAO(false);
        $success = $accountDAO->updatePassword(2, Account::hashPassword(generateRandomString()));

        $this->assertEquals(true, $success);
    }

    public function testUpdateDisplayName()
    {
        $accountDAO = new AccountDAO(false);
        $success = $accountDAO->updateDisplayName(2, "aow");

        $this->assertEquals(true, $success);
    }

    public function testUpdateEmail()
    {
        $accountDAO = new AccountDAO(false);
        $success = $accountDAO->updateEmail(2, generateRandomString(5) . "@notarealemail.address");

        $this->assertEquals(true, $success);
    }
}
