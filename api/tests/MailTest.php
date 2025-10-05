<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . "/../bootstrap.php";

require_once "testUtil.php";

class MailTest extends TestCase
{
    public function testAddMail()
    {
        $mailDAO = new MailDAO(false);
        $mailItems = [];
        for ($i = 1; $i < random_int(2, 5); $i++) {
            $mailItems[] = new MailItem(null, null, generateRandomString(5), strtoupper(generateRandomString(3)), random_int(100, 1000) / 100.0, random_int(100, 1000) / 100.0, random_int(1, 3), generateRandomString(5));
        }

        $service = [
            "name" => "Basic Mail",
            "type" => "Mail",
            "zone" => 1
        ];

        $success = $mailDAO->addMail(
            new Mail(
                null,
                "mBeRQ@notarealemail.address",
                1,
                1,
                $mailItems,
                random_int(5, 20),
                random_int(5, 20),
                random_int(5, 20),
                $service
            )
        );

        $this->assertEquals(true, $success);
    }

    public function testAddMailStatus()
    {
        $mailDAO = new MailDAO(false);
        $success = $mailDAO->addMailStatus(
            new MailStatus(
                null,
                3,
                generateRandomNumbers(3),
                null,
                generateRandomString(10),
                generateRandomString(5)
            )
        );

        $this->assertEquals(true, $success);
    }

    public function testGetMailById()
    {
        $mailDAO = new MailDAO(false);
        // $mailObj = $mailDAO->getMailById()
    }
}
