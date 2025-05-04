<?php

require_once __DIR__ . '/vendor/autoload.php';

use Commission\App;

if ($argc !== 2) {
    fwrite(STDERR, "Usage: php script.php <input_file.csv>\n");
    exit(1);
}

$inputFile = $argv[1];

try {
    // Step 1: Instantiate the App and run with the input file
    $app = new App();
    $results = $app->run($inputFile);

    // Step 2: Output results
    foreach ($results as $result) {
        echo $result . PHP_EOL;
    }
} catch (Throwable $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(1);
}
