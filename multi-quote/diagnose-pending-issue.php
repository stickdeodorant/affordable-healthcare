<?php
/**
 * Simple Pending Leads Diagnostic
 * Checks why leads are stuck in pending status
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output
echo "<!DOCTYPE html>
<html>
<head>
    <title>Pending Leads Check</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .error { background: #ffe4e1; padding: 15px; border-left: 5px solid #ff0000; margin: 10px 0; }
        .success { background: #e1ffe1; padding: 15px; border-left: 5px solid #00ff00; margin: 10px 0; }
        .warning { background: #fff8dc; padding: 15px; border-left: 5px solid #ffa500; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background: #4CAF50; color: white; }
        .code { background: #f0f0f0; padding: 10px; font-family: monospace; margin: 10px 0; }
        .btn { padding: 10px 20px; background: #4CAF50; color: white; border: none; cursor: pointer; margin: 5px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 Pending Leads Diagnostic</h1>";

// Include database config
$configFile = __DIR__ . '/inc/config/db-config.php';
if (!file_exists($configFile)) {
    echo "<div class='error'>Cannot find db-config.php at: $configFile</div>";
    echo "</div></body></html>";
    exit;
}

require_once $configFile;

// Get connection
$mysqli = @getDBConnection();
if (!$mysqli) {
    echo "<div class='error'>Database connection failed. Check db-config.php</div>";
    echo "</div></body></html>";
    exit;
}

echo "<div class='success'>✅ Database connected successfully</div>";

// 1. Check total pending leads
$query = "SELECT COUNT(*) as total FROM lead_buffer WHERE boberdoo_status = 'pending'";
$result = $mysqli->query($query);

if (!$result) {
    echo "<div class='error'>Query failed: " . $mysqli->error . "</div>";
    echo "</div></body></html>";
    exit;
}

$row = $result->fetch_assoc();
$totalPending = $row['total'];

echo "<div class='warning'>";
echo "<h2>Found $totalPending Pending Leads</h2>";
echo "</div>";

// 2. Check how many have responses
$query = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN boberdoo_response IS NOT NULL AND boberdoo_response != '' THEN 1 ELSE 0 END) as with_response,
        SUM(CASE WHEN boberdoo_response IS NULL OR boberdoo_response = '' THEN 1 ELSE 0 END) as without_response
    FROM lead_buffer 
    WHERE boberdoo_status = 'pending'
";

$result = $mysqli->query($query);

if ($result) {
    $stats = $result->fetch_assoc();
    
    echo "<h2>Response Analysis:</h2>";
    echo "<table>";
    echo "<tr><th>Category</th><th>Count</th></tr>";
    echo "<tr><td>Pending WITH boberdoo_response</td><td>{$stats['with_response']}</td></tr>";
    echo "<tr><td>Pending WITHOUT boberdoo_response</td><td>{$stats['without_response']}</td></tr>";
    echo "</table>";
    
    if ($stats['without_response'] > 0) {
        echo "<div class='error'>";
        echo "<h3>❌ Problem Found!</h3>";
        echo "<p>{$stats['without_response']} leads have NO response from Boberdoo.</p>";
        echo "<p>This means the API call is failing or the response isn't being saved.</p>";
        echo "</div>";
    }
}

// 3. Show sample of pending leads
echo "<h2>Sample Pending Leads (First 5):</h2>";

$query = "
    SELECT 
        id,
        lead_id,
        email,
        created_at,
        boberdoo_response,
        boberdoo_lead_id
    FROM lead_buffer 
    WHERE boberdoo_status = 'pending' 
    ORDER BY created_at DESC 
    LIMIT 5
";

$result = $mysqli->query($query);

if ($result && $result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Lead ID</th><th>Email</th><th>Created</th><th>Has Response?</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $hasResponse = !empty($row['boberdoo_response']) ? 'Yes' : 'No';
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['lead_id']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "<td>$hasResponse</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 4. Check if there are leads with responses but still pending
$query = "
    SELECT COUNT(*) as count 
    FROM lead_buffer 
    WHERE boberdoo_status = 'pending' 
    AND boberdoo_response IS NOT NULL 
    AND boberdoo_response != ''
    AND boberdoo_response LIKE '%success%'
";

$result = $mysqli->query($query);
$row = $result->fetch_assoc();

if ($row['count'] > 0) {
    echo "<div class='error'>";
    echo "<h3>⚠️ Critical Issue!</h3>";
    echo "<p>{$row['count']} leads have 'success' in their response but are still marked as pending!</p>";
    echo "<p>The status update code is not working properly.</p>";
    echo "</div>";
}

// 5. The Fix
echo "<h2>The Fix:</h2>";

echo "<div class='success'>";
echo "<h3>Option 1: Fix form-processing.php</h3>";
echo "<p>In <strong>/multi-quote/inc/form-processing.php</strong>:</p>";
echo "<div class='code'>";
echo "Find line ~405:<br>";
echo "\$boberdooResponse = \$apiResponse;<br><br>";
echo "Move it BEFORE the if statement (around line 373):<br>";
echo "\$boberdooResponse = \$apiResponse; // Move this here<br>";
echo "if (\$httpCode === 200 && \$apiResponse) {<br>";
echo "&nbsp;&nbsp;&nbsp;&nbsp;// rest of code...<br>";
echo "}";
echo "</div>";
echo "</div>";

echo "<div class='success'>";
echo "<h3>Option 2: Fix Existing Pending Leads</h3>";

if (isset($_POST['action'])) {
    if ($_POST['action'] === 'fix_no_response') {
        // Mark leads without responses as error
        $update = "
            UPDATE lead_buffer 
            SET boberdoo_status = 'error',
                boberdoo_error = 'No API response captured',
                updated_at = NOW()
            WHERE boberdoo_status = 'pending'
            AND (boberdoo_response IS NULL OR boberdoo_response = '')
        ";
        
        $mysqli->query($update);
        $affected = $mysqli->affected_rows;
        echo "<div class='success'>✅ Updated $affected leads to 'error' status</div>";
        
    } elseif ($_POST['action'] === 'parse_responses') {
        // Parse existing responses to fix status
        $select = "
            SELECT id, boberdoo_response 
            FROM lead_buffer 
            WHERE boberdoo_status = 'pending' 
            AND boberdoo_response IS NOT NULL 
            AND boberdoo_response != ''
            LIMIT 100
        ";
        
        $result = $mysqli->query($select);
        $fixed = 0;
        
        while ($row = $result->fetch_assoc()) {
            $response = $row['boberdoo_response'];
            $status = 'error';
            $leadId = null;
            
            // Check for success indicators
            if (stripos($response, 'success') !== false || stripos($response, '<status>Success</status>') !== false) {
                $status = 'success';
                // Try to extract lead_id
                if (preg_match('/<lead_id>([^<]+)<\/lead_id>/i', $response, $matches)) {
                    $leadId = $matches[1];
                }
            }
            
            $update = "UPDATE lead_buffer SET boberdoo_status = ?, boberdoo_lead_id = ? WHERE id = ?";
            $stmt = $mysqli->prepare($update);
            $stmt->bind_param("ssi", $status, $leadId, $row['id']);
            if ($stmt->execute()) {
                $fixed++;
            }
            $stmt->close();
        }
        
        echo "<div class='success'>✅ Fixed $fixed leads by parsing their responses</div>";
    }
}

echo "<form method='POST' style='margin-top: 20px;'>";
echo "<button type='submit' name='action' value='fix_no_response' class='btn'>Mark No-Response Leads as Error</button>";
echo "<button type='submit' name='action' value='parse_responses' class='btn'>Parse Existing Responses</button>";
echo "</form>";
echo "</div>";

echo "</div></body></html>";

$mysqli->close();
?>