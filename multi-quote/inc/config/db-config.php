<?php
require_once __DIR__ . '/../../../inc/env.php';
/**
 * Centralized Database Configuration - Updated
 * Handles both phone and primary_phone fields properly
 */

// Database constants (fall back to legacy values for production safety)
defined('DB_HOST') || define('DB_HOST', env('MQ_DB_HOST', env('DB_HOST', 'localhost')));
defined('DB_USER') || define('DB_USER', env('MQ_DB_USER', env('DB_USER', 'healthca_leads')));
defined('DB_PASS') || define('DB_PASS', env('MQ_DB_PASS', env('DB_PASS', '')));
defined('DB_NAME') || define('DB_NAME', env('MQ_DB_NAME', env('DB_NAME', 'healthca_leads')));  // Standardized database

/**
 * Get database connection with error handling
 */
function getDBConnection() {
    try {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($mysqli->connect_error) {
            error_log("Database connection failed: " . $mysqli->connect_error);
            throw new Exception("Database connection failed");
        }
        
        $mysqli->set_charset("utf8mb4");
        return $mysqli;
        
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

/**
 * Safe ID generation (PHP 5.6+ compatible)
 */
function generateLeadId($prefix = 'LEAD') {
    $timestamp = date('YmdHis');
    $random = mt_rand(100000, 999999);
    $unique = substr(uniqid(), -4);
    return $prefix . '-' . $timestamp . '-' . $random . '-' . $unique;
}

/**
 * Insert lead into buffer with phone field handling
 */
function insertLeadToBuffer($data) {
    $mysqli = getDBConnection();
    if (!$mysqli) return false;
    
    // Generate lead ID if not provided
    $leadId = isset($data['lead_id']) ? $data['lead_id'] : generateLeadId();
    
    // Handle phone fields - use same value for both if only one provided
    $phone = isset($data['phone']) ? $data['phone'] : (isset($data['primary_phone']) ? $data['primary_phone'] : '');
    $primary_phone = isset($data['primary_phone']) ? $data['primary_phone'] : $phone;
    
    // Prepare the query
    $sql = "INSERT INTO lead_buffer (
        lead_id,
        email,
        phone,
        primary_phone,
        first_name,
        last_name,
        city,
        state,
        zip,
        ip_address,
        boberdoo_status,
        expires_at,
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', DATE_ADD(NOW(), INTERVAL 7 DAY), NOW())";
    
    $stmt = $mysqli->prepare($sql);
    
    if ($stmt) {
        // Set defaults for missing fields
        $email = isset($data['email']) ? $data['email'] : '';
        $first_name = isset($data['first_name']) ? $data['first_name'] : '';
        $last_name = isset($data['last_name']) ? $data['last_name'] : '';
        $city = isset($data['city']) ? $data['city'] : '';
        $state = isset($data['state']) ? $data['state'] : '';
        $zip = isset($data['zip']) ? $data['zip'] : '';
        $ip_address = isset($data['ip_address']) ? $data['ip_address'] : $_SERVER['REMOTE_ADDR'];
        
        $stmt->bind_param("ssssssssss",
            $leadId,
            $email,
            $phone,
            $primary_phone,
            $first_name,
            $last_name,
            $city,
            $state,
            $zip,
            $ip_address
        );
        
        $success = $stmt->execute();
        $insertId = $success ? $mysqli->insert_id : 0;
        
        if (!$success) {
            error_log("Lead insert failed: " . $stmt->error);
        }
        
        $stmt->close();
        $mysqli->close();
        
        return $insertId;
    } else {
        error_log("Lead insert prepare failed: " . $mysqli->error);
        $mysqli->close();
        return false;
    }
}

/**
 * Log system events
 */
function logSystemEvent($type, $status, $details = '') {
    $mysqli = getDBConnection();
    if ($mysqli) {
        $stmt = $mysqli->prepare("
            INSERT INTO system_health_log (check_type, status, details)
            VALUES (?, ?, ?)
        ");
        if ($stmt) {
            $stmt->bind_param("sss", $type, $status, $details);
            $stmt->execute();
            $stmt->close();
        }
        $mysqli->close();
    }
}

/**
 * Check if table exists
 */
function tableExists($tableName) {
    $mysqli = getDBConnection();
    if ($mysqli) {
        $result = $mysqli->query("SHOW TABLES LIKE '$tableName'");
        $exists = $result->num_rows > 0;
        $mysqli->close();
        return $exists;
    }
    return false;
}

/**
 * Get table column information
 */
function getTableColumns($tableName) {
    $mysqli = getDBConnection();
    if (!$mysqli) return [];
    
    $columns = [];
    $result = $mysqli->query("SHOW COLUMNS FROM $tableName");
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $columns[$row['Field']] = [
                'type' => $row['Type'],
                'null' => $row['Null'],
                'default' => $row['Default']
            ];
        }
    }
    
    $mysqli->close();
    return $columns;
}
?>