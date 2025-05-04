<?php

declare(strict_types=1);

namespace App\Contract;

interface ExchangeRateProviderInterface
{
    /**
     * Gets exchange rates relative to the base currency.
     *
     * @return array<string, float> Associative array like ['USD' => 1.1, 'JPY' => 130.0]
     */
    public function getRates(string $baseCurrency): array;
}