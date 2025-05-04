<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\ExchangeRateProviderInterface;

class CurrencyConverter
{
    private ExchangeRateProviderInterface $rateProvider;
    private string $baseCurrency;
    private array $rates = [];

    public function __construct(ExchangeRateProviderInterface $rateProvider, string $baseCurrency = BASE_CURRENCY)
    {
        $this->rateProvider = $rateProvider;
        $this->baseCurrency = $baseCurrency;
    }

    private function loadRatesIfNeeded(): void
    {
        if (empty($this->rates)) {
            $this->rates = $this->rateProvider->getRates($this->baseCurrency);
            $this->rates[$this->baseCurrency] = 1.0; // Ensure base currency rate is 1
        }
    }

    /**
     * Converts an amount from one currency to another.
     * For simplicity, converts From -> Base -> To if direct rate isn't obvious.
     *
     * @param string $amount       Amount as a string for precision
     * @param string $fromCurrency Source currency code (e.g., 'USD')
     * @param string $toCurrency   Target currency code (e.g., 'EUR')
     * @return string              Converted amount as a string
     */
    public function convert(string $amount, string $fromCurrency, string $toCurrency): string
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        $this->loadRatesIfNeeded();

        if (!isset($this->rates[$fromCurrency])) {
            throw new \InvalidArgumentException("Unknown source currency: {$fromCurrency}");
        }
        if (!isset($this->rates[$toCurrency])) {
            throw new \InvalidArgumentException("Unknown target currency: {$toCurrency}");
        }

        $amountFloat = floatval($amount);

        $amountInBase = $amountFloat / $this->rates[$fromCurrency];

        $convertedAmountFloat = $amountInBase * $this->rates[$toCurrency];


        return (string) $convertedAmountFloat;
    }

    public function convertToEur(string $amount, string $currency): string
    {
        return $this->convert($amount, $currency, $this->baseCurrency);
    }
}