# Axios connections

## accounts.php

| Request Method | Parameters                                                                    | response.data returns                      |
| -------------- | ----------------------------------------------------------------------------- | ------------------------------------------ |
| POST           | method: addAccount <br> displayName: <br> email: <br> password: <br> isStaff: | message                                    |
| POST           | method: getAccountById <br> accountId:                                        | message <br> if successful: account object |
| POST           | method: getAccountByEmail <br> email:                                         | message <br> if successful: account object |

## mail.php

| Request Method | Parameters                                                                                                                                                                                                                                                                                                                                                 | response.data returns                                   |
| -------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------- |
| POST           | method: addMail <br> customerEmail: <br> senderAddressId: <br> recipientAddressId: <br> mailItems: {array of mailItem arrays, with keys ["itemDescription", "declaredCurrency", "declaredValue", "itemWeight", "itemQuantity", "hsCode"]} <br> parcelLength: <br> parcelWidth: <br> parcelHeight: <br> service: {array with keys ["name", "type", "zone"]} | message, success <br> if successful: mailId             |
| POST           | method: addMailStatus <br> mailId: <br> statusCode: <br> statusTimestamp: <br> statusDescription: <br> statusLocation:                                                                                                                                                                                                                                     | message                                                 |
| POST           | method: getMailById <br> mailId:                                                                                                                                                                                                                                                                                                                           | message <br> if successful: mail object                 |
| POST           | method: getAllMailByCustomerEmail <br> customerEmail:                                                                                                                                                                                                                                                                                                      | message <br> if successful: array of mail objects       |
| POST           | method: getMailItemByItemId <br> itemId:                                                                                                                                                                                                                                                                                                                   | message <br> if successful: mailItem object             |
| POST           | method: getMailStatusesByMailId <br> mailId:                                                                                                                                                                                                                                                                                                               | message <br> if successful: array of mailStatus objects |
| POST           | method: getMailStatusByStatusId <br> statusId                                                                                                                                                                                                                                                                                                              | message <br> if successful: mailStatus object           |
| POST           | method: getServiceRate <br> name: <br> type: <br> zone:                                                                                                                                                                                                                                                                                                    | message <br> if successful: serviceRate object          |
| POST           | method: getServiceTime <br> name: <br> type: <br> zone:                                                                                                                                                                                                                                                                                                    | message <br> if successful: serviceTime object          |
| POST           | method: isMailPaid <br> mailId:                                                                                                                                                                                                                                                                                                                            | isMailPaid                                              |
| POST           | method: getMailTrackingNum <br> mailId:                                                                                                                                                                                                                                                                                                                    | trackingNum                                             |
| POST           | method: getMatchingServices <br> zone: <br> type: <br> weight: <br> height: <br> width: <br> length:                                                                                                                                                                                                                                                       | services object                                         |
| POST           | method: getServiceInfo <br> name: <br> type:                                                                                                                                                                                                                                                                                                               | message <br> if successful: info object                 |
| POST           | method: getZone <br> countryCode:                                                                                                                                                                                                                                                                                                                          | message <br> if successful: zone                        |
| POST           | method: getCountryCode <br> countryName:                                                                                                                                                                                                                                                                                                                   | message <br> if successful: countryCode                 |

## addresses.php

| Request Method | Parameters                                                                                                                                                                                                                                 | response.data returns                          |
| -------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ---------------------------------------------- |
| POST           | method: addAddress <br> name: <br> email: <br> phone: <br> phoneCountryCode: <br> address: {array with keys ["addressLines", "postalCode", "countryCode"] where "addressLines" is an array with 3 values, one value for each address line} | message, success <br> if successful: addressId |
| POST           | method: getAddressById <br> addressId:                                                                                                                                                                                                     | message <br> if successful: address object     |

# Classes

## ConnectionManager

Constructor: bool $useServer = true

## Address

Constructor: ?int $addressId, string $name, string $email, ?int $phone, ?string $phoneCountryCode, array $address

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
- getPhoneNumberString(): ?string
- getPhone(): ?int
- getPhoneCountryCode(): ?string
- setAddressId(int $addressId): void
- jsonSerialize(): mixed

## MailItem

Constructor: ?int $itemId, ?int $mailId, string $itemDescription, string $declaredCurrency, float $declaredValue, float $itemWeight, int $itemQuantity, ?string $hsCode

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
- jsonSerialize(): mixed

## MailStatus

Constructor: ?int $statusId, int $mailId, int $statusCode, ?int $statusTimestamp, string $statusDescription, string $statusLocation

Methods:

- getStatusId(): ?int
- getMailId(): int
- getStatusCode(): int
- getStatusTimeStamp(): ?int
- getStatusDescription(): string
- getStatusLocation(): string
- setStatusId(int $statusId): void
- setStatusTimestamp(int $statusTimestamp): void
- jsonSerialize(): mixed

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
- jsonSerialize(): mixed

## MailDAO

Constructor: bool $userServer = true

Methods:

- addAddress(Address $newAddress): mixed (int or bool)
- addMail(Mail $newMail): bool
- addMailStatus(MailStatus $newMailStatus): bool
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
- getMatchingServices(int $zone, string $type, float $weight, float $height, float $width, float $length): array of $service with keys ["name", "min", "max", "basecost", "baseweight", "addcost", "addweight"]
- getServiceInfo(array $service): ?array with keys ["name", "type", "isTracked", "maxWeight", "maxHeight", "maxWidth", "maxLength"]
- getZone(string $countryCode): ?int
- getCountryCode(string $countryName): ?string

## Account

Constructor: ?int $accountId, string $displayName, string $email, string $passwordHashed, bool $isStaff

Static methods:

- hashPassword(string $password): string

Methods:

- getAccountId(): int
- getDisplayName(): string
- getEmail(): string
- isStaff(): bool
- getPasswordHashed(): string
- verifyPassword(string $password): bool
- setAccountId(int $accountId): void
- jsonSerialize(): mixed

## AccountDAO

Constructor: bool $userServer = true

Methods:

- addAccount(Account $newAccount): bool
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

- testAddMail
- testAddMailStatus
- testGetMailById
- testGetAllMailByCustomerEmail
- testGetAddressById
- testGetMailItemByItemId
- testGetMailStatusesByMailId
- testGetMailStatusByStatusId
- testGetServiceRate
- testGetServiceTime
- testIsMailPaid
- testGetMailTrackingNum
- testGetMatchingServices
- testGetServiceInfo
- testGetZone
- testGetCountryCode

# Run PHPUnit (if installed)

```sh
./vendor/bin/phpunit
```
