<?php

declare(strict_types=1);

namespace App\Dto;

use DateTimeImmutable;

class Operation
{
    private DateTimeImmutable $date;
    private int $userId;
    private string $userType; // 'private' or 'business'
    private string $operationType; // 'deposit' or 'withdraw'
    private string $amount; // Use string for precision
    private string $currency;

    public function __construct(
        string $date,
        int $userId,
        string $userType,
        string $operationType,
        string $amount,
        string $currency
    ) {
        $this->date = new DateTimeImmutable($date);
        $this->userId = $userId;
        $this->userType = $userType;
        $this->operationType = $operationType;
        $this->amount = $amount;
        $this->currency = $currency;

        // Basic validation (can be expanded)
        if (!in_array($userType, ['private', 'business'])) {
            throw new \InvalidArgumentException("Invalid user type: {$userType}");
        }
        if (!in_array($operationType, ['deposit', 'withdraw'])) {
            throw new \InvalidArgumentException("Invalid operation type: {$operationType}");
        }
        if (!is_numeric($amount) || floatval($amount) < 0) {
            throw new \InvalidArgumentException("Invalid amount: {$amount}");
        }
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getUserType(): string
    {
        return $this->userType;
    }

    public function getOperationType(): string
    {
        return $this->operationType;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }
}