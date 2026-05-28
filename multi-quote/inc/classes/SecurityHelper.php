<?php

class SecurityHelper {
    private static $csrfTokenName = 'csrf_token';
    private static $csrfTokenLength = 32;
    
    /**
     * Initialize security settings
     */
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        self::setSecurityHeaders();
        self::generateCSRFToken();
    }
    
    /**
     * Set security headers
     */
    public static function setSecurityHeaders() {
        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        
        // XSS Protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Content type sniffing protection
        header('X-Content-Type-Options: nosniff');
        
        // Referrer policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://code.jquery.com; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; " .
               "font-src 'self' https://fonts.gstatic.com; " .
               "img-src 'self' data: https:; " .
               "connect-src 'self' https://zip.getziptastic.com https://api.trustedform.com;";
        header("Content-Security-Policy: $csp");
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION[self::$csrfTokenName])) {
            $_SESSION[self::$csrfTokenName] = bin2hex(random_bytes(self::$csrfTokenLength));
        }
        return $_SESSION[self::$csrfTokenName];
    }
    
    /**
     * Get CSRF token
     */
    public static function getCSRFToken() {
        return $_SESSION[self::$csrfTokenName] ?? self::generateCSRFToken();
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCSRFToken($token) {
        if (!isset($_SESSION[self::$csrfTokenName])) {
            return false;
        }
        
        return hash_equals($_SESSION[self::$csrfTokenName], $token);
    }
    
    /**
     * Generate CSRF field HTML
     */
    public static function generateCSRFField() {
        $token = self::getCSRFToken();
        return '<input type="hidden" name="' . self::$csrfTokenName . '" value="' . self::escape($token) . '">';
    }
    
    /**
     * Escape output for HTML
     */
    public static function escape($string, $encoding = 'UTF-8') {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, $encoding);
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitize($data, $type = 'string') {
        if (is_array($data)) {
            return array_map(function($item) use ($type) {
                return self::sanitize($item, $type);
            }, $data);
        }
        
        switch ($type) {
            case 'email':
                return filter_var($data, FILTER_SANITIZE_EMAIL);
                
            case 'url':
                return filter_var($data, FILTER_SANITIZE_URL);
                
            case 'int':
                return filter_var($data, FILTER_SANITIZE_NUMBER_INT);
                
            case 'float':
                return filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                
            case 'phone':
                return preg_replace('/[^0-9+\-\(\) ]/', '', $data);
                
            case 'alpha':
                return preg_replace('/[^a-zA-Z]/', '', $data);
                
            case 'alphanumeric':
                return preg_replace('/[^a-zA-Z0-9]/', '', $data);
                
            case 'name':
                return preg_replace('/[^a-zA-Z\s\-\']/', '', $data);
                
            case 'zip':
                return preg_replace('/[^0-9\-]/', '', $data);
                
            default:
                return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }
    
    /**
     * Validate input data
     */
    public static function validate($data, $type, $options = []) {
        switch ($type) {
            case 'email':
                return filter_var($data, FILTER_VALIDATE_EMAIL) !== false;
                
            case 'url':
                return filter_var($data, FILTER_VALIDATE_URL) !== false;
                
            case 'phone':
                $phone = preg_replace('/[^0-9]/', '', $data);
                return strlen($phone) === 10 || strlen($phone) === 11;
                
            case 'zip':
                return preg_match('/^\d{5}(-\d{4})?$/', $data);
                
            case 'required':
                return !empty($data);
                
            case 'min_length':
                return strlen($data) >= ($options['length'] ?? 0);
                
            case 'max_length':
                return strlen($data) <= ($options['length'] ?? PHP_INT_MAX);
                
            case 'regex':
                return preg_match($options['pattern'] ?? '/.*/', $data);
                
            default:
                return true;
        }
    }
    
    /**
     * Generate random string
     */
    public static function generateRandomString($length = 16) {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Hash password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Rate limiting check
     */
    public static function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 300) {
        $key = 'rate_limit_' . md5($identifier);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'first_attempt' => time()
            ];
        }
        
        $data = &$_SESSION[$key];
        
        // Reset if time window has passed
        if (time() - $data['first_attempt'] > $timeWindow) {
            $data['attempts'] = 0;
            $data['first_attempt'] = time();
        }
        
        $data['attempts']++;
        
        return $data['attempts'] <= $maxAttempts;
    }
    
    /**
     * Log security event
     */
    public static function logSecurityEvent($event, $details = []) {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'details' => $details
        ];
        
        error_log('[SECURITY] ' . json_encode($logData));
    }
}