<?php
require_once __DIR__ . '/../../../inc/env.php';
// inc/config/app.php

class AppConfig {
    private static $instance = null;
    private $config = [];
    
    private function __construct() {
        $gtmContainers = env_array('GTM_CONTAINERS', ['GTM-PQPDQHX']);
        $gaIds = env_array('GA_MEASUREMENT_IDS', ['UA-203937944-1']);

        $this->config = [
            'site_name' => env('SITE_NAME', 'Affordable Healthcare'),
            'domain' => env('APP_DOMAIN', $_SERVER['HTTP_HOST'] ?? 'affordable-healthcare.com'),
            'environment' => env('APP_ENV', 'production'),
            
            // Database settings
            'database' => [
                'host' => env('MQ_DB_HOST', env('DB_HOST', 'localhost')),
                'name' => env('MQ_DB_NAME', env('DB_NAME', 'healthcare')),
                'user' => env('MQ_DB_USER', env('DB_USER', 'root')),
                'pass' => env('MQ_DB_PASS', env('DB_PASS', ''))
            ],
            
            // Phone numbers
            'phones' => [
                'typ' => env('PHONE_TYP', '(866) 670-4997'),
                'popup' => env('PHONE_POPUP', '(866) 670-4993'),
                'standard' => env('PHONE_STANDARD', '(866) 303-0563'),
                'premium' => env('PHONE_PREMIUM', '(866) 472-8453'),
                'h2' => env('PHONE_H2', '(855) 329-5850'),
                'medicare' => env('PHONE_MEDICARE', '(866) 670-7041')
            ],
            
            // States configuration
            'states' => [
                'excluded' => ['MA', 'NY'],
                'h2_states' => ['AL', 'FL', 'GA', 'KS', 'MS', 'MO', 'NC', 'OH', 'OK', 'SC', 'TN', 'TX'],
                'all' => [
                    'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
                    'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut',
                    'DE' => 'Delaware', 'DC' => 'District of Columbia', 'FL' => 'Florida',
                    'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho', 'IL' => 'Illinois',
                    'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas', 'KY' => 'Kentucky',
                    'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland', 'MA' => 'Massachusetts',
                    'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi', 'MO' => 'Missouri',
                    'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada', 'NH' => 'New Hampshire',
                    'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York', 'NC' => 'North Carolina',
                    'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma', 'OR' => 'Oregon',
                    'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
                    'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah',
                    'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia',
                    'WI' => 'Wisconsin', 'WY' => 'Wyoming'
                ]
            ],
            
            // Analytics
            'analytics' => [
                'enabled' => env_bool('ENABLE_ANALYTICS', true),
                'gtm_id' => $gtmContainers[0] ?? 'GTM-PQPDQHX',
                'ga_id' => $gaIds[0] ?? 'UA-203937944-1'
            ],
            
            // API endpoints
            'api' => [
                'base_url' => env('API_BASE_URL', 'https://affordable-healthcare.com'),
                'zip_api' => env('ZIP_API_URL', 'https://zip.getziptastic.com/v2/US/'),
                'trusted_form' => env('TRUSTED_FORM_URL', 'https://api.trustedform.com/trustedform.js')
            ],
            
            // Security settings
            'security' => [
                'session_lifetime' => 3600,
                'rate_limit_attempts' => 5,
                'rate_limit_window' => 300
            ],
            
            // Tracking
            'pivot_lpid' => env('PIVOT_LPID', '1003')
        ];
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get configuration value
     */
    public function get($key, $default = null) {
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
    
    /**
     * Set configuration value
     */
    public function set($key, $value) {
        $keys = explode('.', $key);
        $config = &$this->config;
        
        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                $config[$k] = $value;
            } else {
                if (!isset($config[$k]) || !is_array($config[$k])) {
                    $config[$k] = [];
                }
                $config = &$config[$k];
            }
        }
    }
    
    /**
     * Get state name by abbreviation
     */
    public function getStateName($abbreviation) {
        $states = $this->get('states.all', []);
        return $states[strtoupper($abbreviation)] ?? $abbreviation;
    }
    
    /**
     * Check if state is excluded
     */
    public function isStateExcluded($state) {
        $excluded = $this->get('states.excluded', []);
        return in_array(strtoupper($state), $excluded);
    }
    
    /**
     * Check if state is H2
     */
    public function isH2State($state) {
        $h2States = $this->get('states.h2_states', []);
        return in_array(strtoupper($state), $h2States);
    }
}