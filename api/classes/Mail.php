<?php

class Mail implements JsonSerializable
{
    private ?int $mailId;
    private string $customerEmail;
    private int $senderAddressId;
    private int $recipientAddressId;
    private array $mailItems;
    private float $parcelLength;
    private float $parcelWidth;
    private float $parcelHeight;
    private string $serviceName;
    private string $serviceType;
    private string $serviceZone;

    public function __construct(
        ?int $mailId,
        string $customerEmail,
        int $senderAddressId,
        int $recipientAddressId,
        array $mailItems,
        float $parcelLength,
        float $parcelWidth,
        float $parcelHeight,
        array $service
    ) {
        $this->mailId = $mailId;
        $this->customerEmail = $customerEmail;
        $this->senderAddressId = $senderAddressId;
        $this->recipientAddressId = $recipientAddressId;
        $this->mailItems = $mailItems;
        $this->parcelLength = $parcelLength;
        $this->parcelWidth = $parcelWidth;
        $this->parcelHeight = $parcelHeight;
        $this->serviceName = $service["name"];
        $this->serviceType = $service["type"];
        $this->serviceZone = $service["zone"];
    }

    public function getMailId(): ?int
    {
        return $this->mailId;
    }

    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }

    public function getSenderAddressId(): int
    {
        return $this->senderAddressId;
    }

    public function getRecipientAddressId(): int
    {
        return $this->recipientAddressId;
    }

    public function getMailItems(): array // of MailItem
    {
        return $this->mailItems;
    }

    public function getParcelLength(): float
    {
        return $this->parcelLength;
    }

    public function getParcelWidth(): float
    {
        return $this->parcelWidth;
    }

    public function getParcelHeight(): float
    {
        return $this->parcelHeight;
    }

    public function getService(): array
    {
        return [
            "name" => $this->serviceName,
            "type" => $this->serviceType,
            "zone" => $this->serviceZone
        ];
    }

    public function getTotalWeight(): float
    {
        $totalWeight = 0;
        foreach ($this->mailItems as $mailItem) {
            $totalWeight += $mailItem->getItemWeight();
        }
        return $totalWeight;
    }

    public function getPostageRate(): float
    {
        $rates = (new MailDAO())->getServiceRate($this->getService());

        $baseRate = $rates["baseRate"];
        $addRate = $rates["addRate"];

        $baseWeight = $rates["baseWeight"];
        $addWeight = $rates["addWeight"];

        $rate = $baseRate;

        if ($this->getTotalWeight() - $baseWeight <= 0) {
            return $rate;
        }

        $remWeight = $this->getTotalWeight() - $baseWeight;

        while ($remWeight > 0) {
            $rate += $addRate;
            $remWeight -= $addWeight;
        }

        return $rate;
    }

    public function setMailId(int $mailId): void
    {
        $this->mailId = $mailId;
    }

    public function jsonSerialize(): mixed
    {
        return get_object_vars($this);
    }
}
