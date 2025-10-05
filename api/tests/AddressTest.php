<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . "/../bootstrap.php";

require_once "testUtil.php";

class AddressTest extends TestCase
{
    public function testAddAddress()
    {
        $mailDAO = new MailDAO(false);
        $success = $mailDAO->addAddress(
            new Address(
                null,
                generateRandomLetters(10),
                generateRandomString(5) . "@fakeemail.address",
                generateRandomNumbers(8),
                generateRandomNumbers(2),
                [
                    "addressLines" => [
                        "Block " . generateRandomNumbers(3),
                        "Street " . generateRandomLetters(5),
                        "Building " . generateRandomString(1)
                    ],
                    "postalCode" => generateRandomNumbers(6),
                    "countryCode" => generateRandomLetters(2)
                ]
            )
        );

        $this->assertEquals(true, $success);
    }

    public function testGetAddressById()
    {
        $mailDAO = new MailDAO(false);

        $addressObj = $mailDAO->getAddressById(1);

        $this->assertEquals("aqBmhMCWVF", $addressObj->getName());
    }
}
