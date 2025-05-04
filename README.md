# Commission Fee Calculator

This application calculates commission fees for financial transactions based on provided rules, reading input from a CSV file.

## Features

* Calculates fees for deposit and withdraw operations.
* Handles different rules for 'private' and 'business' clients.
* Applies weekly withdrawal limits (amount and frequency) for private clients.
* Performs currency conversions using exchange rates (requires configuration).
* Rounds fees up based on currency precision.
* Processes CSV files efficiently using streaming (suitable for large files).
* Built with maintainability and extensibility in mind (PSR-4, Services, DI).

## Requirements

* PHP >= 7.4 (or ^8.0 as specified in `composer.json`)
* Composer

## Installation

1.  Clone the repository:
    ```bash
    clone repository
    cd commission-calculator
    ```
2.  Install dependencies:
    ```bash
    composer install
    ```
3.  **(Optional but Recommended for API)** If using the `ApiExchangeRateProvider`, consider installing Guzzle:
    ```bash
    composer require guzzlehttp/guzzle
    ```
    *You would then update `ApiExchangeRateProvider.php` to use Guzzle instead of `file_get_contents`.*
4.  **Configure API Key:** Edit `config/app.php` and replace `'YOUR_PSEUDO_API_KEY'` with a valid API key if you are using a live exchange rate API that requires one. Verify the `api_base_url` is correct for your chosen provider.

## How to Run
Execute the main script from the command line, providing the path to the input CSV file as an argument:
```bash
 php bin/commission_calculator.php path/to/your/input.csv
```

## How to Run Tests
Execute the main script from the command line, providing the path to the input CSV file as an argument:
```bash
 vendor/bin/phpunit
```