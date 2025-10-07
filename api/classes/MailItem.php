<?php

class MailItem
{
    private ?int $itemId;
    private ?int $mailId;

    private string $itemDescription;
    private string $declaredCurrency; // 3 char currency code
    private float $declaredValue;
    private float $itemWeight;
    private int $itemQuantity;
    private ?string $hsCode;

    public function __construct(
        ?int $itemId,
        ?int $mailId,
        string $itemDescription,
        string $declaredCurrency,
        float $declaredValue,
        float $itemWeight,
        int $itemQuantity,
        ?string $hsCode
    ) {
        $this->itemId = $itemId;
        $this->mailId = $mailId;
        $this->itemDescription = $itemDescription;
        $this->declaredCurrency = $declaredCurrency;
        $this->declaredValue = $declaredValue;
        $this->itemWeight = $itemWeight;
        $this->itemQuantity = $itemQuantity;
        $this->hsCode = $hsCode;
    }

    public function getItemId(): ?int
    {
        return $this->itemId;
    }

    public function getMailId(): ?int
    {
        return $this->mailId;
    }

    public function getItemDescription(): string
    {
        return $this->itemDescription;
    }

    public function getDeclaredCurrency(): string
    {
        return $this->declaredCurrency;
    }

    public function getDeclaredValue(): float
    {
        return $this->declaredValue;
    }

    public function getItemWeight(): float
    {
        return $this->itemWeight;
    }

    public function getItemQuantity(): int
    {
        return $this->itemQuantity;
    }

    public function getHsCode(): string
    {
        return $this->hsCode;
    }

    public function setItemId(int $itemId): void
    {
        $this->itemId = $itemId;
    }
}
