<?php

declare(strict_types=1);

namespace App\Service\Calculator;

use App\Config\Config;
use App\Contract\FeeCalculatorInterface;
use App\Dto\Operation;
use App\Service\RoundingService;
use App\Service\Trackers\WeeklyLimitManager;

class WithdrawFeeCalculator implements FeeCalculatorInterface
{
    private Config $config;
    private RoundingService $roundingService;
    private WeeklyLimitManager $limitManager;

    public function __construct(
        Config $config,
        RoundingService $roundingService,
        WeeklyLimitManager $limitManager
    ) {
        $this->config = $config;
        $this->roundingService = $roundingService;
        $this->limitManager = $limitManager;
    }

    public function calculate(Operation $operation): string
    {
        $feeRate = (string) $this->getFeeRate($operation->getUserType());
        $commissionableAmount = $operation->getAmount();

        if ($operation->getUserType() === 'private') {
            $commissionableAmount = $this->limitManager->processWithdrawal($operation);
        }

        if (bccomp($commissionableAmount, '0', 10) <= 0) {
            return $this->roundingService->roundUp('0', $operation->getCurrency());
        }

        $fee = bcmul($commissionableAmount, $feeRate, 10); // High precision
        return $this->roundingService->roundUp($fee, $operation->getCurrency());
    }


    private function getFeeRate(string $userType): string
    {
        $rate = $this->config->get('fees.withdraw.' . $userType . '.rate');
        if ($rate === null) {
            throw new \InvalidArgumentException("Withdraw fee rate not configured for user type: {$userType}");
        }
        return (string)$rate;
    }
}