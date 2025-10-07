<?php

use PharIo\Manifest\ManifestDocumentLoadingException;
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
            "name" => "Basic Package",
            "type" => "Packets",
            "zone" => 1
        ];

        $success = $mailDAO->addMail(
            new Mail(
                null,
                "muhheeow@fakeemail.com",
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
                1,
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
        $mailObj = $mailDAO->getMailById(1);
        $this->assertEquals("muhheeow@fakeemail.com", $mailObj->getCustomerEmail());
    }

    public function testGetAllMailByCustomerEmail()
    {
        $mailDAO = new MailDAO(false);
        $mailArr = $mailDAO->getAllMailByCustomerEmail("muhheeow@fakeemail.com");
        $this->assertEquals(true, sizeof($mailArr) > 0);
    }

    public function testGetAddressById()
    {
        $mailDAO = new MailDAO(false);
        $address = $mailDAO->getAddressById(1);
        $this->assertEquals("Cotton cat", $address->getName());
    }

    public function testGetMailItemByItemId()
    {
        $mailDAO = new MailDAO(false);
        $item = $mailDAO->getMailItemByItemId(1);
        $this->assertEquals("Cat food", $item->getItemDescription());
    }

    public function testGetMailStatusesByMailId()
    {
        $mailDAO = new MailDAO(false);
        $statusArr = $mailDAO->getMailStatusesByMailId(1);
        $this->assertEquals(true, sizeof($statusArr) > 0);
    }

    public function testGetMailStatusByStatusId()
    {
        $mailDAO = new MailDAO(false);
        $status = $mailDAO->getMailStatusByStatusId(1);
        $this->assertEquals(200, $status->getStatusCode());
    }

    public function testGetServiceRate()
    {
        $mailDAO = new MailDAO(false);

        $service = [
            "name" => "Basic Mail",
            "type" => "Documents",
            "zone" => 1
        ];

        $rates = $mailDAO->getServiceRate($service);

        $this->assertEquals(0.85, $rates["baseRate"]);
    }

    public function testGetServiceTime()
    {
        $mailDAO = new MailDAO(false);

        $service = [
            "name" => "Basic Mail",
            "type" => "Documents",
            "zone" => 1
        ];

        $period = $mailDAO->getServiceTime($service);

        $this->assertEquals(5, $period["min"]);
    }

    public function testIsMailPaid()
    {
        $mailDAO = new MailDAO(false);

        $paid = $mailDAO->isMailPaid(1);

        $this->assertEquals(false, $paid);
    }

    public function testGetMailTrackingNum()
    {
        $mailDAO = new MailDAO(false);

        $trackingNum = $mailDAO->getMailTrackingNum(1);

        $this->assertEquals(0, $trackingNum);
    }
}
