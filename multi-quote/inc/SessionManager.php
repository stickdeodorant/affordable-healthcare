<?php

class SessionManager {
    private static $sessionName = 'healthcare_session';
    private static $sessionLifetime = 3600; // 1 hour
    
    /**
     * Initialize session with secure settings
     */
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session parameters
            ini_set('session.use_only_cookies', 1);
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.gc_maxlifetime', self::$sessionLifetime);
            
            session_name(self::$sessionName);
            session_start();
            
            // TEMPORARILY DISABLE session regeneration for debugging
            // self::regenerateSession();
        }
    }
    
    /**
     * Regenerate session ID for security
     */
    private static function regenerateSession() {
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 300) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
    
    /**
     * Set location data in session
     */
    public static function setLocation($city, $state, $zip) {
        self::init();
        $_SESSION['location'] = [
            'city' => SecurityHelper::sanitize($city, 'name'),
            'state' => SecurityHelper::sanitize($state, 'alpha'),
            'zip' => SecurityHelper::sanitize($zip, 'zip'),
            'timestamp' => time()
        ];
    }
    
    /**
     * Get city from session
     */
    public static function getCity() {
        self::init();
        return $_SESSION['location']['city'] ?? 
               SecurityHelper::sanitize($_GET['city'] ?? '', 'name');
    }
    
    /**
     * Get state from session
     */
    public static function getState() {
        self::init();
        return $_SESSION['location']['state'] ?? 
               SecurityHelper::sanitize($_GET['state'] ?? '', 'alpha');
    }

    /**
     * Get full state name from session
     */
    public static function getStateName() {
        self::init();
        return $_SESSION['form_data']['state_name'] ?? 
            $_SESSION['location']['state_name'] ?? 
            self::getState(); // Fallback to abbreviation
    }
    
    /**
     * Get ZIP from session
     */
    public static function getZip() {
        self::init();
        return $_SESSION['location']['zip'] ?? 
               SecurityHelper::sanitize($_GET['zip'] ?? '', 'zip');
    }
    
    /**
     * Store form data
     */
    public static function setFormData($key, $value) {
        self::init();
        if (!isset($_SESSION['form_data'])) {
            $_SESSION['form_data'] = [];
        }
        $_SESSION['form_data'][$key] = SecurityHelper::sanitize($value);
    }
    
    /**
     * Get form data
     */
    public static function getFormData($key = null) {
        self::init();
        if ($key === null) {
            return $_SESSION['form_data'] ?? [];
        }
        return $_SESSION['form_data'][$key] ?? null;
    }
    
    /**
     * Clear form data
     */
    public static function clearFormData() {
        self::init();
        unset($_SESSION['form_data']);
    }
    
    /**
     * Set current form step
     */
    public static function setCurrentStep($step) {
        self::init();
        $_SESSION['current_step'] = intval($step);
    }
    
    /**
     * Get current form step
     */
    public static function getCurrentStep() {
        self::init();
        return $_SESSION['current_step'] ?? 1;
    }
    
    /**
     * Destroy session
     */
    public static function destroy() {
        session_destroy();
        $_SESSION = [];
    }
}