<?php

declare(strict_types=1);

namespace App\Service\Calculator;

use App\Config\Config;
use App\Contract\FeeCalculatorInterface;
use App\Dto\Operation;
use App\Service\RoundingService;

class DepositFeeCalculator implements FeeCalculatorInterface
{
    private Config $config;
    private RoundingService $roundingService;
    private string $feeRate;

    public function __construct(Config $config, RoundingService $roundingService)
    {
        $this->config = $config;
        $this->roundingService = $roundingService;
        $this->feeRate = (string) $this->config->get('fees.deposit');
    }

    public function calculate(Operation $operation): string
    {
        $amount = $operation->getAmount();
        $fee = bcmul($amount, $this->feeRate, 8); // multiply with high precision

        // Round up the calculated fee
        return $this->roundingService->roundUp($fee, $operation->getCurrency());
    }
}
