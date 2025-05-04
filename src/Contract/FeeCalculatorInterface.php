<?php

declare(strict_types=1);

namespace App\Contract;

use App\Dto\Operation;

interface FeeCalculatorInterface
{
    /**
     * Calculate the commission fee for the given operation.
     *
     * @param Operation $operation
     * @return string The calculated fee amount as a string.
     */
    public function calculate(Operation $operation): string;
}