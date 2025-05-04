<?php

declare(strict_types=1);

namespace App\Config;

class Config
{
    private array $config;

    public function __construct(string $configFile = __DIR__ . '/../../config/app.php')
    {
        if (!file_exists($configFile)) {
            throw new \RuntimeException("Configuration file not found: {$configFile}");
        }
        $this->config = require $configFile;
    }

    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        return $value;
    }
}