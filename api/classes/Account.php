<?php

class Account
{
    private int $accountId;
    private string $displayName;
    private string $email;
    private string $passwordHashed;
    private bool $isStaff;

    public function __construct(
        int $accountId,
        string $displayName,
        string $email,
        string $passwordHashed,
        bool $isStaff
    ) {
        $this->accountId = $accountId;
        $this->displayName = $displayName;
        $this->email = $email;
        $this->passwordHashed = $passwordHashed;
        $this->isStaff = $isStaff;
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function isStaff(): bool
    {
        return $this->isStaff;
    }

    public function getPasswordHashed(): string
    {
        return $this->passwordHashed;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHashed);
    }
}
