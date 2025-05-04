<?php

declare(strict_types=1);

namespace App\Service\ExchangeRate;

use App\Config\Config;
use App\Contract\ExchangeRateProviderInterface;

class ApiExchangeRateProvider implements ExchangeRateProviderInterface
{
    private Config $config;
    private array $ratesCache = []; // Simple cache per instance run

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getRates(string $baseCurrency): array
    {
        // Basic caching to avoid multiple calls within the same script execution
        if (isset($this->ratesCache[$baseCurrency])) {
            return $this->ratesCache[$baseCurrency];
        }

        $apiKey = $this->config->get('api_key');
        $baseUrl = $this->config->get('api_base_url');
        // Example URL structure - adjust if needed for the specific API provider
        $url = $baseUrl . 'latest?access_key=' . $apiKey . '&base=' . $baseCurrency;

        // Use Guzzle or another HTTP client here in a real application
        $responseJson = @file_get_contents($url); // Using @ to suppress errors for brevity, don't do this in production!

        if ($responseJson === false) {
            // Handle error appropriately - log, throw exception, use fallback, etc.
            // For this example, we'll throw an exception.
            throw new \RuntimeException("Failed to fetch exchange rates from API.");
        }

        $responseData = json_decode($responseJson, true);

        if (
            !$responseData ||
            !isset($responseData['rates']) ||
            (isset($responseData['success']) && $responseData['success'] === false) // Common API pattern
        ) {
            // Log actual response data for debugging
            error_log("Invalid API response: " . $responseJson);
            throw new \RuntimeException("Invalid response received from exchange rate API.");
        }

        // Convert rates to float
        $rates = array_map('floatval', $responseData['rates']);
        $this->ratesCache[$baseCurrency] = $rates;

        return $rates;
    }
}