<?php

class MailStatus implements JsonSerializable
{
    private ?int $statusId;
    private int $mailId;

    private int $statusCode;

    private ?int $statusTimestamp; // integer, unix timestamp

    private string $statusDescription;

    private string $statusLocation;

    public function __construct(?int $statusId, int $mailId, int $statusCode, ?int $statusTimestamp, string $statusDescription, string $statusLocation)
    {
        $this->statusId = $statusId;
        $this->mailId = $mailId;
        $this->statusCode = $statusCode;
        $this->statusTimestamp = $statusTimestamp;
        $this->statusDescription = $statusDescription;
        $this->statusLocation = $statusLocation;
    }

    public function getStatusId(): ?int
    {
        return $this->statusId;
    }

    public function getMailId(): int
    {
        return $this->mailId;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getStatusTimestamp(): ?int
    {
        return $this->statusTimestamp;
    }

    public function getStatusDescription(): string
    {
        return $this->statusDescription;
    }

    public function getStatusLocation(): string
    {
        return $this->statusLocation;
    }

    public function setStatusId(int $statusId): void
    {
        $this->statusId = $statusId;
    }

    public function setStatusTimestamp(int $statusTimestamp): void
    {
        $this->statusTimestamp = $statusTimestamp;
    }

    public function jsonSerialize(): mixed
    {
        return get_object_vars($this);
    }
}
