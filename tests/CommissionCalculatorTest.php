<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Config\Config;
use App\Contract\ExchangeRateProviderInterface;
use App\Dto\Operation;
use App\Service\CurrencyConverter;
use App\Service\RoundingService;
use App\Service\Trackers\WeeklyLimitManager;
use App\Service\Calculator\DepositFeeCalculator;
use App\Service\Calculator\WithdrawFeeCalculator;
use App\Service\CommissionService;
use App\Service\CsvReader;
use Prophecy\PhpUnit\ProphecyTrait; // Prophecy for mocking external services

class CommissionCalculatorTest extends TestCase
{
    use ProphecyTrait; // Enable Prophecy integration for mocking

    private string $inputFile = __DIR__ . '/Fixtures/input.csv';
    private string $expectedOutputFile = __DIR__ . '/Fixtures/expected_output.txt';

    // Mock Exchange Rate Provider to return fixed rates for the test
    private function getMockRateProvider(): ExchangeRateProviderInterface
    {
        $providerProphecy = $this->prophesize(ExchangeRateProviderInterface::class);

        // Define the fixed rates based on the example
        $rates = [
            'USD' => 1.1497,
            'JPY' => 129.53,
        ];

        // Expect getRates to be called with 'EUR' and return the fixed rates
        $providerProphecy->getRates('EUR')->willReturn($rates);

        return $providerProphecy->reveal(); // Return the mock object
    }

    public function testCalculationsMatchExampleOutput(): void
    {
        // Check if the required files exist
        if (!file_exists($this->inputFile)) {
            $this->markTestSkipped("Input fixture file not found: {$this->inputFile}");
        }
        if (!file_exists($this->expectedOutputFile)) {
            $this->markTestSkipped("Expected output fixture file not found: {$this->expectedOutputFile}");
        }

        // --- Build services with Mocked Exchange Rate Provider ---
        $config = new Config();
        $rateProvider = $this->getMockRateProvider(); // Use the mock

        // Assume BASE_CURRENCY is already set in your app configuration
        if (!defined('BASE_CURRENCY')) {
            define('BASE_CURRENCY', 'EUR');
        }

        $currencyConverter = new CurrencyConverter($rateProvider, BASE_CURRENCY);
        $roundingService = new RoundingService($config);
        $weeklyLimitManager = new WeeklyLimitManager($config, $currencyConverter);

        $depositCalculator = new DepositFeeCalculator($config, $roundingService);
        $withdrawCalculator = new WithdrawFeeCalculator($config, $roundingService, $weeklyLimitManager);
        $commissionService = new CommissionService($depositCalculator, $withdrawCalculator);
        $csvReader = new CsvReader($this->inputFile);
        // --- End Service Setup ---

        // --- Process and Collect Actual Output ---
        $actualOutput = [];
        foreach ($csvReader->readOperations() as $operation) {
            // Recalculate fee for each operation using the services
            $fee = $commissionService->calculateFee($operation);
            $actualOutput[] = $fee;
        }

        // --- Compare with Expected Output ---
        $expectedOutputRaw = file_get_contents($this->expectedOutputFile);
        $expectedOutput = explode("\n", trim($expectedOutputRaw)); // Trim trailing newline

        $this->assertEquals($expectedOutput, $actualOutput, "Calculated fees do not match expected output.");
    }
}
