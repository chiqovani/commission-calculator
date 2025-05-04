<?php

declare(strict_types=1);

namespace App\Util;

use DateTimeInterface;

class WeekHelper
{
    /**
     * Gets a unique identifier for the week (Monday-Sunday) of a given date.
     * Uses ISO-8601 week date format (YYYY-Www).
     */
    public static function getWeekIdentifier(DateTimeInterface $date): string
    {
        return $date->format('o-\WW');
    }
}