# Classes

## Address

Constructor: int $addressId, string $name, string $email, int $phone, int $phoneCountryCode, array $address
Format of array $address:
[
    "address" => [$addressLine1, $addressLine2, $addressLine3],
"postalCode" => $postalCode,
"countryCode" => $countryCode
]

Methods:

- getAddressId(): int
- getName(): int
- getEmail(): string
- getAddress(): array as above

## MailItem

Constructor: int $itemId, int $mailId, string $itemDescription, string $declaredCurrency, float $declaredValue, int $itemQuantity, string $hsCode

Methods:

- getItemId(): int
- getMailId(): int
- getItemDescription(): string
- getDeclaredCurrency(): string
- getDeclaredValue(): float
- getItemWeight(): float
- getItemQuantity(): int
- getHsCode(): string

## MailStatus

Constructor: int $statusId, int $mailId, int $statusCode, int $statusTimestamp, string $statusDescription, string $statusLocation

Methods:

- getStatusId(): int
- getMailId(): int
- getStatusCode(): int
- getStatusTimeStamp(): int
- getStatusDescription(): string
- getStatusLocation(): string

## Mail

Constructor: int $mailId, int $customerEmail, int $senderAddressId, int $recipientAddressId, array $mailItems, float $parcelLength, float $parcelWidth, float $parcelHeight, array $service
Format of array $service:
[
"name" => $serviceName,
"type" => $serviceType,
"zone" => $serviceZone
]

Methods:

- getMailId(): int
- getCustomerEmail(): string
- getSenderAddressId(): int
- getRecipientAddressId(): int
- getMailItems(): array of MailItem
- getParcelLength(): float
- getParcelWidth(): float
- getParcelHeight(): float
- getService(): array as above
- getTotalWeight(): float
- getPostageRate(): float

## MailDAO

Static methods:

- insertMail(Mail $mailObj): void
- getMailById(int $mailId): Mail
- getAllMailByCustomerEmail(string $customerEmail): array of Mail
- getAddressById(int $addressId): Address
- getMailItemById(int $mailItemId): MailItem
- getMailStatusesById(int $mailId): array of MailStatus
- getServiceRate(array $service): array $rates
  - $rates structure:
    [
    "baseRate" => $baseRate,
    "addRate" => $addRate,
    "baseWeight" => $baseWeight,
    "addWeight" => $addWeight
    ]
- getServiceTime(array $service): array $period
  - $period structure:
    [
    "min" => $minDays,
    "max" => $maxDays
    ]
- isMailPaid(int $mailId): bool
- getMailTrackingNum(int $mailId): int
