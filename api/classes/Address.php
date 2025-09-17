<?php

class Address
{
    private int $addressId;

    private string $name;

    private string $email;

    private int $phone;
    private int $phoneCountryCode;

    private string $addressLine1;
    private string $addressLine2;
    private string $addressLine3;
    private string $postalCode;
    private string $countryCode;

    public function __construct(
        int $addressId,
        string $name,
        string $email,
        int $phone,
        int $phoneCountryCode,
        array $address
    ) {
        /*
        format of $address:
            [
                "address" => [$addressLine1, $addressLine2, $addressLine3],
                "postalCode" => $postalCode,
                "countryCode" => $countryCode
            ]
        */
        $this->addressId = $addressId;
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->phoneCountryCode = $phoneCountryCode;
        $this->addressLine1 = $address["address"][0];
        $this->addressLine2 = $address["address"][1];
        $this->addressLine3 = $address["address"][2];
        $this->postalCode = $address["postalCode"];
        $this->countryCode = $address["countryCode"];
    }

    public function getAddressId(): int
    {
        return $this->addressId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getAddress(): array
    {
        return [
            "address" => [$this->addressLine1, $this->addressLine2, $this->addressLine3],
            "postalCode" => $this->postalCode,
            "countryCode" => $this->countryCode
        ];
    }

    public function getPhoneNumberString(): string
    {
        return "+" . $this->phoneCountryCode . " " . $this->phone;
    }
}
