<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Operation;
use Generator; // Use Generator for efficient iteration

class CsvReader
{
    private string $filePath;

    public function __construct(string $filePath)
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \InvalidArgumentException("File not found or not readable: {$filePath}");
        }
        $this->filePath = $filePath;
    }

    /**
     * Reads the CSV file line by line and yields Operation objects.
     *
     * @return Generator<Operation>
     */
    public function readOperations(): Generator
    {
        $handle = fopen($this->filePath, 'r');
        if ($handle === false) {
            throw new \RuntimeException("Could not open file: " . $this->filePath);
        }

        try {
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) !== 6) {
                    // Log warning or throw exception for invalid rows
                    error_log("Skipping invalid row: " . implode(',', $row));
                    continue;
                }
                try {
                    // Yield an Operation DTO
                    yield new Operation(
                        $row[0], // date
                        (int)$row[1], // user id
                        $row[2], // user type
                        $row[3], // operation type
                        $row[4], // amount
                        $row[5]  // currency
                    );
                } catch (\InvalidArgumentException $e) {
                    error_log("Skipping row due to invalid data: " . implode(',', $row) . " - Error: " . $e->getMessage());
                    continue; // Skip rows with invalid data according to DTO validation
                } catch (\Exception $e) {
                    // Catch potential date parsing errors etc.
                    error_log("Skipping row due to error: " . implode(',', $row) . " - Error: " . $e->getMessage());
                    continue;
                }
            }
        } finally {
            // Ensure the file handle is always closed
            fclose($handle);
        }
    }
}