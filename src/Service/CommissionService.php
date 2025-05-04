<?php

declare(strict_types=1);

namespace App\Service;

use App\Config\Config;
use App\Contract\FeeCalculatorInterface;
use App\Dto\Operation;
use App\Service\Calculator\DepositFeeCalculator;
use App\Service\Calculator\WithdrawFeeCalculator;
use App\Service\Trackers\WeeklyLimitManager; // Needed for Withdraw calculator dependency

class CommissionService
{
    private FeeCalculatorInterface $depositCalculator;
    private FeeCalculatorInterface $withdrawCalculator;

    // Could use a Factory pattern here for more complex scenarios
    public function __construct(
        DepositFeeCalculator $depositCalculator,
        WithdrawFeeCalculator $withdrawCalculator
    ) {
        $this->depositCalculator = $depositCalculator;
        $this->withdrawCalculator = $withdrawCalculator;
    }

    public function calculateFee(Operation $operation): string
    {
        switch ($operation->getOperationType()) {
            case 'deposit':
                return $this->depositCalculator->calculate($operation);
            case 'withdraw':
                return $this->withdrawCalculator->calculate($operation);
            default:
                // Should not happen if Operation DTO validation is good
                throw new \InvalidArgumentException("Unsupported operation type: " . $operation->getOperationType());
        }
    }
}