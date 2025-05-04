<?php

declare(strict_types=1);

namespace App\Service;

use App\Config\Config;

class RoundingService
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Rounds the amount UP to the specified currency's precision.
     *
     * @param float|string $amount   The amount to round.
     * @param string       $currency The currency code.
     * @return string      The rounded amount as a string, formatted to the correct decimals.
     */
    public function roundUp(string $amount, string $currency): string
    {
        $precision = $this->config->get('currency_precision.' . $currency);

        if ($precision === null) {
            throw new \InvalidArgumentException("Precision not defined for currency: {$currency}");
        }

        $multiplier = pow(10, $precision);
        $floatAmount = (float)$amount;

        // Multiply, ceil, then divide
        $rounded = ceil($floatAmount * $multiplier) / $multiplier;

        return number_format($rounded, $precision, '.', '');
    }



}