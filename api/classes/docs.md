# to be updated (it's oudated :( )

# Run PHPUnit (if installed)

```sh
./vendor/bin/phpunit
```

# Classes

## ConnectionManager

Constructor: bool $useServer = true

## Address

Constructor: ?int $addressId, string $name, string $email, int $phone, int $phoneCountryCode, array $address

Format of array $address:
[
    "addressLines" => [$addressLine1, $addressLine2, $addressLine3],
"postalCode" => $postalCode,
"countryCode" => $countryCode
]

Methods:

- getAddressId(): ?int
- getName(): int
- getEmail(): string
- getAddress(): array as above

## MailItem

Constructor: ?int $itemId, ?int $mailId, string $itemDescription, string $declaredCurrency, float $declaredValue, int $itemQuantity, string $hsCode

Methods:

- getItemId(): ?int
- getMailId(): ?int
- getItemDescription(): string
- getDeclaredCurrency(): string
- getDeclaredValue(): float
- getItemWeight(): float
- getItemQuantity(): int
- getHsCode(): string
- setItemId(int $ItemId): void

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

Constructor: ?int $mailId, string $customerEmail, int $senderAddressId, int $recipientAddressId, array $mailItems, float $parcelLength, float $parcelWidth, float $parcelHeight, array $service

Format of array $service:
[
"name" => $serviceName,
"type" => $serviceType,
"zone" => $serviceZone
]

Methods:

- getMailId(): ?int
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
- setMailId(int $mailId): void

## MailDAO

Constructor: bool $userServer = true

Methods:

- addAddress(string $name, string $email, int $phone, string $phoneCountryCode, array $address): bool
- addMail(int $customerEmail, int $senderAddressId, int $recipientAddressId, array $mailItems, float $parcelLength, float $parcelWidth, float $parcelHeight, array $service): bool
- addMailStatus(int $mailId, int $statusCode, string $statusDescription, string $statusLocation): bool
- getMailById(int $mailId): ?Mail
- getAllMailByCustomerEmail(string $customerEmail): array of Mail
- getAddressById(int $addressId): ?Address
- getMailItemByItemId(int $itemId): ?MailItem
- getMailStatusesByMailId(int $mailId): array of MailStatus
- getMailStatusByStatusId(int $statusId): ?MailStatus
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

## Account

Constructor: int $accountId, string $displayName, string $email, string $passwordHashed, bool $isStaff

Static methods:

- hashPassword(string $password): string

Methods:

- getAccountId(): int
- getDisplayName(): string
- getEmail(): string
- isStaff(): bool
- getPasswordHashed(): string
- verifyPassword(string $password): bool

## AccountDAO

Constructor: bool $userServer = true

Methods:

- addAccount(string $displayName, string $email, string $passwordHashed, bool $isStaff): bool
- getAccountById(int $accountId): ?Account
- getAccountByEmail(string $email): ?Account
- updatePassword(int $accountId, string $newPasswordHashed): bool
- updateDisplayName(int $accountId, string $newDisplayName): bool
- updateEmail(int $accountId, string $newEmail): bool

# Tests

## AccountTest.php

Tests Account, AccountDAO:

- testAddAccount
- testGetAccountById
- testGetAccountByEmail
- testUpdatePassword
- testUpdateDisplayName
- testUpdateEmail

## AddressTest.php

Tests Address, MailDAO

- testAddAddress
- testGetAddressById

## MailTest.php

Tests Mail, MailItem, MailStatus, MailDAO

- testAddMail (tbd)
- testAddMailStatus
