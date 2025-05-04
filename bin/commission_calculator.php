<?php

declare(strict_types=1);

// Bootstrap autoloading and services
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Config;
use App\Contract\ExchangeRateProviderInterface;
use App\Service\ExchangeRate\ApiExchangeRateProvider;
use App\Service\CurrencyConverter;
use App\Service\RoundingService;
use App\Service\Trackers\WeeklyLimitManager;
use App\Service\Calculator\DepositFeeCalculator;
use App\Service\Calculator\WithdrawFeeCalculator;
use App\Service\CommissionService;
use App\Service\CsvReader;

if ($argc < 2) {
    echo "Usage: php " . $argv[0] . " <input_csv_file>\n";
    exit(1);
}

$inputFile = $argv[1];

try {
    // --- Dependency Injection Container (Manual Setup) ---
    // In a larger app, use a DI container library (like Symfony DI, PHP-DI)
    $config = new Config();

    // Choose Exchange Rate Provider (replace with mock/stub for testing if needed)
    // Consider adding error handling or fallback if API fails
    $rateProvider = new ApiExchangeRateProvider($config);

    $currencyConverter = new CurrencyConverter($rateProvider, BASE_CURRENCY);
    $roundingService = new RoundingService($config);

    // WeeklyLimitManager needs converter for private withdrawals check
    $weeklyLimitManager = new WeeklyLimitManager($config, $currencyConverter);

    // Fee Calculators
    $depositCalculator = new DepositFeeCalculator($config, $roundingService);
    $withdrawCalculator = new WithdrawFeeCalculator($config, $roundingService, $weeklyLimitManager);

    // Main Service
    $commissionService = new CommissionService($depositCalculator, $withdrawCalculator);

    // Input Reader
    $csvReader = new CsvReader($inputFile);
    // --- End DI Setup ---


    // --- Process Operations ---
    foreach ($csvReader->readOperations() as $operation) {
        $fee = $commissionService->calculateFee($operation);
        echo $fee . "\n";
    }

} catch (\Exception $e) {
    // Catch potential errors during setup or processing
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(1);
}

exit(0); // Success