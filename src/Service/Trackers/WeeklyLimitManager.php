<?php

declare(strict_types=1);

namespace App\Service\Trackers;

use App\Dto\Operation;
use App\Service\CurrencyConverter;
use App\Config\Config;
use App\Util\WeekHelper;

class WeeklyLimitManager
{
    private Config $config;
    private CurrencyConverter $converter;

    /**
     * Stores weekly withdrawal data:
     * [userId => [weekId => ['count' => int, 'amountEur' => string]]]
     */
    private array $weeklyData = [];

    private string $freeLimit;
    private int $freeOperationsLimit;

    public function __construct(Config $config, CurrencyConverter $converter)
    {
        $this->config = $config;
        $this->converter = $converter;
        $this->freeLimit = (string)$this->config->get('fees.withdraw.private.weekly_free_limit');
        $this->freeOperationsLimit = (int)$this->config->get('fees.withdraw.private.free_operations_limit');
    }

    public function processWithdrawal(Operation $operation): string
    {
        if ($operation->getUserType() !== 'private' || $operation->getOperationType() !== 'withdraw') {
            return $operation->getAmount();
        }

        $userId = $operation->getUserId();
        $weekId = WeekHelper::getWeekIdentifier($operation->getDate());
        $operationAmount = $operation->getAmount();
        $operationCurrency = $operation->getCurrency();

        if (!isset($this->weeklyData[$userId][$weekId])) {
            $this->weeklyData[$userId][$weekId] = [
                'count' => 0,
                'amountEur' => '0',
            ];
        }

        $userData = &$this->weeklyData[$userId][$weekId];

        $currentCount = $userData['count'];
        $currentAmountEur = $userData['amountEur'];

        $commissionableAmount = '0';
        $amountToAddToTotalEur = '0';

        $operationAmountEur = $this->converter->convertToEur($operationAmount, $operationCurrency);

        if (
            $currentCount >= $this->freeOperationsLimit ||
            bccomp($currentAmountEur, $this->freeLimit, 2) >= 0
        ) {
            $commissionableAmount = $operationAmount;
            $amountToAddToTotalEur = $operationAmountEur;
        } else {
            $remainingFreeLimit = bcsub($this->freeLimit, $currentAmountEur, 8);

            if (bccomp($operationAmountEur, $remainingFreeLimit, 8) <= 0) {
                $commissionableAmount = '0';
                $amountToAddToTotalEur = $operationAmountEur;
            } else {
                $commissionableAmountEur = bcsub($operationAmountEur, $remainingFreeLimit, 8);
                $ratio = bcdiv($commissionableAmountEur, $operationAmountEur, 10);
                $commissionableAmount = bcmul($operationAmount, $ratio, 8);
                $amountToAddToTotalEur = $operationAmountEur;
            }
        }

        $userData['count']++;
        $userData['amountEur'] = bcadd($currentAmountEur, $amountToAddToTotalEur, 8);

        return $commissionableAmount;
    }
}
