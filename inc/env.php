<?php
/**
 * Environment Configuration Loader
 * 
 * Auto-detects local vs production environment based on:
 * 1. APP_ENV environment variable (if set)
 * 2. Server hostname/domain detection
 * 
 * Usage:
 *   env('DB_PASS')           - Get a value
 *   env('DB_PASS', 'default') - Get with fallback
 *   env_bool('APP_DEBUG')    - Get as boolean
 *   is_production()          - Check if production
 *   is_local()               - Check if local
 */

if (!function_exists('env_load')) {
    function env_parse_file($envPath) {
        $envData = [];

        if (!is_readable($envPath)) {
            return $envData;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return $envData;
        }

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || $trimmed[0] === '#' || $trimmed[0] === ';') {
                continue;
            }

            $separatorPos = strpos($line, '=');
            if ($separatorPos === false) {
                continue;
            }

            $key = trim(substr($line, 0, $separatorPos));
            $value = trim(substr($line, $separatorPos + 1));

            if ($key === '' || preg_match('/\s/', $key)) {
                continue;
            }

            $firstChar = substr($value, 0, 1);
            $lastChar = substr($value, -1);
            if (($firstChar === '"' && $lastChar === '"') || ($firstChar === "'" && $lastChar === "'")) {
                $quote = $firstChar;
                $value = substr($value, 1, -1);

                if ($quote === '"') {
                    $value = str_replace(['\\"', '\\\\'], ['"', '\\'], $value);
                }
            }

            $envData[$key] = $value;
        }

        return $envData;
    }

    function env_load() {
        static $envData = null;
        if ($envData !== null) {
            return $envData;
        }

        $envData = [];
        $envPath = dirname(__DIR__) . '/.env';

        $envData = env_parse_file($envPath);

        return $envData;
    }
}

if (!function_exists('env')) {
    function env($key, $default = null) {
        $envData = env_load();

        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }

        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        if (array_key_exists($key, $envData)) {
            return $envData[$key];
        }

        return $default;
    }
}

if (!function_exists('env_bool')) {
    function env_bool($key, $default = false) {
        $value = env($key, null);
        if ($value === null) {
            return (bool)$default;
        }

        $normalized = strtolower(trim((string)$value));
        if ($normalized === '') {
            return (bool)$default;
        }

        return in_array($normalized, ['1', 'true', 'on', 'yes'], true);
    }
}

if (!function_exists('env_array')) {
    function env_array($key, $default = [], $delimiter = ',') {
        $value = env($key, null);
        if ($value === null || $value === '') {
            return $default;
        }

        if (is_array($value)) {
            return $value;
        }

        $items = array_map('trim', explode($delimiter, $value));
        return array_values(array_filter($items, 'strlen')) ?: $default;
    }
}

/**
 * Auto-detect environment based on hostname/domain
 * Returns: 'production', 'staging', or 'local'
 */
if (!function_exists('detect_environment')) {
    function detect_environment() {
        // First check if APP_ENV is explicitly set
        $appEnv = env('APP_ENV');
        if ($appEnv && in_array($appEnv, ['production', 'staging', 'local', 'development'])) {
            return ($appEnv === 'development') ? 'local' : $appEnv;
        }
        
        // Auto-detect based on server variables
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
        $host = strtolower($host);
        
        // Production domains (add your production domains here)
        $productionDomains = [
            'affordable-healthcare.com',
            'www.affordable-healthcare.com',
            // Add any other production domains
        ];
        
        // Staging domains
        $stagingDomains = [
            'staging.affordable-healthcare.com',
            'test.affordable-healthcare.com',
        ];
        
        // Local development indicators
        $localIndicators = [
            'localhost',
            '127.0.0.1',
            '.local',
            '.test',
            '.dev',
            'ampps',
        ];
        
        // Check production
        foreach ($productionDomains as $domain) {
            $suffix = '.' . $domain;
            if ($host === $domain || substr($host, -strlen($suffix)) === $suffix) {
                return 'production';
            }
        }
        
        // Check staging
        foreach ($stagingDomains as $domain) {
            $suffix = '.' . $domain;
            if ($host === $domain || substr($host, -strlen($suffix)) === $suffix) {
                return 'staging';
            }
        }
        
        // Check local
        foreach ($localIndicators as $indicator) {
            if (strpos($host, $indicator) !== false) {
                return 'local';
            }
        }
        
        // Default to local for safety (won't accidentally run prod code)
        return 'local';
    }
}

/**
 * Check if running in production
 */
if (!function_exists('is_production')) {
    function is_production() {
        return detect_environment() === 'production';
    }
}

/**
 * Check if running in staging
 */
if (!function_exists('is_staging')) {
    function is_staging() {
        return detect_environment() === 'staging';
    }
}

/**
 * Check if running locally
 */
if (!function_exists('is_local')) {
    function is_local() {
        return detect_environment() === 'local';
    }
}

/**
 * Get database connection config from environment
 */
if (!function_exists('get_db_config')) {
    function get_db_config() {
        return [
            'host' => env('DB_HOST', 'localhost'),
            'user' => env('DB_USER', 'afford_leads'),
            'pass' => env('DB_PASS', ''),
            'name' => env('DB_NAME', 'afford_leads'),
        ];
    }
}

if (!function_exists('env_phone_defaults')) {
    function env_phone_defaults() {
        return [
            'typ' => env('PHONE_TYP', '(866) 670-4997'),
            'medicare' => env('PHONE_MEDICARE', '(866) 670-7041'),
            'popup' => env('PHONE_POPUP', '(866) 670-4993'),
            'standard' => env('PHONE_STANDARD', '(866) 303-0563'),
            'premium' => env('PHONE_PREMIUM', '(866) 472-8453'),
            'h2' => env('PHONE_H2', '(855) 329-5850'),
        ];
    }
}

/**
 * Create a mysqli connection using env config
 */
if (!function_exists('get_db_connection')) {
    function get_db_connection() {
        $config = get_db_config();
        $conn = new mysqli($config['host'], $config['user'], $config['pass'], $config['name']);
        
        if ($conn->connect_error) {
            if (is_local()) {
                die("Connection failed: " . $conn->connect_error);
            } else {
                error_log("Database connection failed: " . $conn->connect_error);
                die("Database connection error. Please try again later.");
            }
        }
        
        return $conn;
    }
}
