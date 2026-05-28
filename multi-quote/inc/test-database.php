<?php
require_once __DIR__ . '/../../inc/feature-flags.php';
require_once __DIR__ . '/../../inc/env.php';
if (empty($featureFlags['enable_legacy_pages'])) {
    http_response_code(404);
    exit;
}
/**
 * Database Diagnostic Script
 * Tests database connectivity and operations independently
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

header('Content-Type: text/plain');

echo "=== DATABASE DIAGNOSTIC TEST ===\n\n";

// Database credentials from environment
$dbConfig = [
    'host' => env('DB_HOST', 'localhost'),
    'user' => env('DB_USER', 'healthca_leads'),
    'pass' => env('DB_PASS', ''),
    'name' => env('DB_NAME', 'healthca_leads')
];

echo "1. Testing Database Connection...\n";
echo "   Host: {$dbConfig['host']}\n";
echo "   User: {$dbConfig['user']}\n";
echo "   Database: {$dbConfig['name']}\n\n";

try {
    $mysqli = new mysqli(
        $dbConfig['host'],
        $dbConfig['user'],
        $dbConfig['pass'],
        $dbConfig['name']
    );
    
    if ($mysqli->connect_error) {
        echo "❌ FAILED: " . $mysqli->connect_error . "\n";
        echo "   Error Code: " . $mysqli->connect_errno . "\n\n";
        echo "DIAGNOSIS: Database connection failed.\n";
        echo "- Check if MySQL is running\n";
        echo "- Verify username and password\n";
        echo "- Verify database exists\n";
        exit;
    }
    
    echo "✓ Connected successfully!\n\n";
    
    // Set charset
    $mysqli->set_charset("utf8mb4");
    echo "✓ Charset set to utf8mb4\n\n";
    
} catch (Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
    exit;
}

// Check if tables exist
echo "2. Checking Required Tables...\n";
$requiredTables = ['lead_buffer', 'lead_history', 'email_blacklist'];

foreach ($requiredTables as $table) {
    $result = $mysqli->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "   ✓ Table exists: $table\n";
        
        // Count records
        $countResult = $mysqli->query("SELECT COUNT(*) as cnt FROM $table");
        $count = $countResult->fetch_assoc()['cnt'];
        echo "     Records: $count\n";
    } else {
        echo "   ❌ Table MISSING: $table\n";
    }
}
echo "\n";

// Test INSERT into lead_buffer
echo "3. Testing INSERT into lead_buffer...\n";

$testEmail = 'diagnostictest-' . time() . '@example.com';
$testPhone = '555' . mt_rand(1000000, 9999999);
$testLeadId = 'DIAG-TEST-' . date('YmdHis') . '-' . mt_rand(1000, 9999);
$testExpires = date('Y-m-d H:i:s', strtotime('+7 days'));

echo "   Test Data:\n";
echo "   - Lead ID: $testLeadId\n";
echo "   - Email: $testEmail\n";
echo "   - Phone: $testPhone\n\n";

try {
    // First, check table structure
    echo "   Checking table structure...\n";
    $result = $mysqli->query("DESCRIBE lead_buffer");
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
        echo "     - {$row['Field']} ({$row['Type']})" . ($row['Null'] == 'NO' ? ' NOT NULL' : '') . "\n";
    }
    echo "\n";
    
    // Try INSERT
    echo "   Attempting INSERT...\n";
    
    $stmt = $mysqli->prepare("
        INSERT INTO lead_buffer (
            lead_id,
            first_name,
            last_name,
            email,
            primary_phone,
            gender,
            zip,
            city,
            state,
            ip_address,
            user_agent,
            boberdoo_status,
            expires_at,
            submission_time
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())
    ");
    
    if (!$stmt) {
        echo "   ❌ Prepare failed: " . $mysqli->error . "\n";
        echo "   Error Code: " . $mysqli->errno . "\n\n";
    } else {
        echo "   ✓ Statement prepared\n";
        
        $firstName = 'Diagnostic';
        $lastName = 'Test';
        $gender = 'M';
        $zip = '90210';
        $city = 'Beverly Hills';
        $state = 'CA';
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Diagnostic Test';
        
        $stmt->bind_param("sssssssssssss",
            $testLeadId,
            $firstName,
            $lastName,
            $testEmail,
            $testPhone,
            $gender,
            $zip,
            $city,
            $state,
            $ipAddress,
            $userAgent,
            $testExpires
        );
        
        if ($stmt->execute()) {
            $insertId = $mysqli->insert_id;
            echo "   ✓ INSERT successful! Buffer ID: $insertId\n\n";
            
            // Verify it was saved
            $checkStmt = $mysqli->prepare("SELECT * FROM lead_buffer WHERE id = ?");
            $checkStmt->bind_param("i", $insertId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                $lead = $checkResult->fetch_assoc();
                echo "   ✓ Verification - Record found in database:\n";
                echo "     - Buffer ID: {$lead['id']}\n";
                echo "     - Lead ID: {$lead['lead_id']}\n";
                echo "     - Email: {$lead['email']}\n";
                echo "     - Status: {$lead['boberdoo_status']}\n";
                echo "     - Created: {$lead['created_at']}\n\n";
            } else {
                echo "   ❌ Record NOT found after insert!\n\n";
            }
            
            $checkStmt->close();
        } else {
            echo "   ❌ Execute failed: " . $stmt->error . "\n";
            echo "   Error Code: " . $stmt->errno . "\n\n";
        }
        
        $stmt->close();
    }
    
} catch (Exception $e) {
    echo "   ❌ EXCEPTION: " . $e->getMessage() . "\n\n";
}

// Test INSERT into lead_history
echo "4. Testing INSERT into lead_history...\n";

try {
    echo "   Checking table structure...\n";
    $result = $mysqli->query("DESCRIBE lead_history");
    if (!$result) {
        echo "   ❌ Cannot describe table: " . $mysqli->error . "\n\n";
    } else {
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
            echo "     - {$row['Field']} ({$row['Type']})" . ($row['Null'] == 'NO' ? ' NOT NULL' : '') . "\n";
        }
        echo "\n";
        
        echo "   Attempting INSERT...\n";
        
        $stmt = $mysqli->prepare("
            INSERT INTO lead_history (
                lead_id, email, phone, first_name, last_name,
                gender, city, state, zip,
                ip_address, user_agent,
                boberdoo_status, is_blacklisted, response_time_ms
            ) VALUES (
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?,
                'success', 0, 100
            )
        ");
        
        if (!$stmt) {
            echo "   ❌ Prepare failed: " . $mysqli->error . "\n";
            echo "   Error Code: " . $mysqli->errno . "\n\n";
        } else {
            echo "   ✓ Statement prepared\n";
            
            $firstName = 'Diagnostic';
            $lastName = 'Test';
            $gender = 'M';
            $city = 'Beverly Hills';
            $state = 'CA';
            $zip = '90210';
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Diagnostic Test';
            
            $stmt->bind_param("sssssssssss",
                $testLeadId,
                $testEmail,
                $testPhone,
                $firstName,
                $lastName,
                $gender,
                $city,
                $state,
                $zip,
                $ipAddress,
                $userAgent
            );
            
            if ($stmt->execute()) {
                $insertId = $mysqli->insert_id;
                echo "   ✓ INSERT successful! History ID: $insertId\n\n";
                
                // Verify
                $checkStmt = $mysqli->prepare("SELECT * FROM lead_history WHERE id = ?");
                $checkStmt->bind_param("i", $insertId);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();
                
                if ($checkResult->num_rows > 0) {
                    $lead = $checkResult->fetch_assoc();
                    echo "   ✓ Verification - Record found in database:\n";
                    echo "     - History ID: {$lead['id']}\n";
                    echo "     - Lead ID: {$lead['lead_id']}\n";
                    echo "     - Email: {$lead['email']}\n";
                    echo "     - Status: {$lead['boberdoo_status']}\n";
                    echo "     - Created: {$lead['submission_timestamp']}\n\n";
                } else {
                    echo "   ❌ Record NOT found after insert!\n\n";
                }
                
                $checkStmt->close();
            } else {
                echo "   ❌ Execute failed: " . $stmt->error . "\n";
                echo "   Error Code: " . $stmt->errno . "\n\n";
            }
            
            $stmt->close();
        }
    }
    
} catch (Exception $e) {
    echo "   ❌ EXCEPTION: " . $e->getMessage() . "\n\n";
}

// Check permissions
echo "5. Checking Database User Permissions...\n";
$result = $mysqli->query("SHOW GRANTS FOR CURRENT_USER");
if ($result) {
    while ($row = $result->fetch_array()) {
        echo "   " . $row[0] . "\n";
    }
} else {
    echo "   Cannot retrieve grants: " . $mysqli->error . "\n";
}
echo "\n";

// Check recent leads in both tables
echo "6. Checking Recent Leads (Last 5)...\n\n";

echo "   lead_buffer:\n";
$result = $mysqli->query("SELECT id, lead_id, email, boberdoo_status, created_at FROM lead_buffer ORDER BY id DESC LIMIT 5");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "     - ID:{$row['id']} | {$row['lead_id']} | {$row['email']} | {$row['boberdoo_status']} | {$row['created_at']}\n";
    }
} else {
    echo "     No records found\n";
}
echo "\n";

echo "   lead_history:\n";
$result = $mysqli->query("SELECT id, lead_id, email, boberdoo_status, submission_timestamp FROM lead_history ORDER BY id DESC LIMIT 5");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "     - ID:{$row['id']} | {$row['lead_id']} | {$row['email']} | {$row['boberdoo_status']} | {$row['submission_timestamp']}\n";
    }
} else {
    echo "     No records found\n";
}
echo "\n";

$mysqli->close();

echo "=== DIAGNOSTIC TEST COMPLETE ===\n\n";

echo "SUMMARY:\n";
echo "If you see ✓ marks above, database operations are working.\n";
echo "If you see ❌ marks, that's where the problem is.\n\n";

echo "NEXT STEPS:\n";
echo "1. If tables are missing: Run the table creation SQL\n";
echo "2. If inserts fail: Check the exact error message above\n";
echo "3. If inserts succeed here but not in form-processing.php:\n";
echo "   - The form processing code has a different issue\n";
echo "   - Check that form-processing.php is using the same database\n";
echo "   - Check error logs for form-processing.php execution\n";
?>