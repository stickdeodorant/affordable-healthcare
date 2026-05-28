<?php
/**
 * Fix Lead Buffer Status Mismatch
 * Corrects leads that have successful Boberdoo responses but are marked as error
 */

// Include database configuration
require_once __DIR__ . '/../config/db-config.php';

// Get database connection
$mysqli = getDBConnection();

if (!$mysqli) {
    die("Database connection failed\n");
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Lead Buffer Status Mismatch</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f7fa;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #2196F3;
            padding-bottom: 10px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #2196F3;
        }
        .stat-card h3 {
            margin: 0;
            color: #2196F3;
            font-size: 2em;
        }
        .stat-card p {
            margin: 10px 0 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .status-success { color: #4CAF50; font-weight: bold; }
        .status-error { color: #f44336; font-weight: bold; }
        .status-fixed { color: #FF9800; font-weight: bold; }
        .btn {
            background: #2196F3;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
        }
        .btn:hover {
            background: #1976D2;
        }
        .btn-danger {
            background: #f44336;
        }
        .btn-danger:hover {
            background: #d32f2f;
        }
        .btn-success {
            background: #4CAF50;
        }
        .btn-success:hover {
            background: #388E3C;
        }
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .alert-info {
            background: #e3f2fd;
            color: #1565C0;
            border-left: 4px solid #2196F3;
        }
        .alert-warning {
            background: #fff3e0;
            color: #E65100;
            border-left: 4px solid #FF9800;
        }
        .alert-success {
            background: #e8f5e9;
            color: #2E7D32;
            border-left: 4px solid #4CAF50;
        }
        pre {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
            max-height: 300px;
        }
        .xml-response {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
            max-width: 100%;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Fix Lead Buffer Status Mismatch</h1>
        
        <?php
        // Step 1: Analyze the problem
        echo "<h2>Step 1: Analyzing Status Mismatches</h2>";
        
        // Find leads with successful XML responses but error status
        $analysisQuery = "
            SELECT 
                id,
                lead_id,
                email,
                boberdoo_status,
                boberdoo_response,
                boberdoo_lead_id,
                created_at
            FROM lead_buffer
            WHERE boberdoo_status IN ('error', 'failed', 'rejected')
            AND boberdoo_response IS NOT NULL
            AND (
                boberdoo_response LIKE '%<status>Success</status>%'
                OR boberdoo_response LIKE '%\"status\":\"Success\"%'
                OR boberdoo_lead_id IS NOT NULL
            )
            ORDER BY created_at DESC
        ";
        
        $result = $mysqli->query($analysisQuery);
        $mismatches = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Parse the response to confirm it's actually successful
                $response = $row['boberdoo_response'];
                $isSuccess = false;
                $parsedLeadId = null;
                
                // Check for XML success response
                if (strpos($response, '<?xml') !== false) {
                    if (preg_match('/<status>Success<\/status>/i', $response)) {
                        $isSuccess = true;
                        if (preg_match('/<lead_id>(\d+)<\/lead_id>/', $response, $matches)) {
                            $parsedLeadId = $matches[1];
                        }
                    }
                }
                // Check for JSON success response
                elseif (strpos($response, '{') === 0) {
                    $json = json_decode($response, true);
                    if ($json && isset($json['status']) && strtolower($json['status']) === 'success') {
                        $isSuccess = true;
                        $parsedLeadId = $json['lead_id'] ?? null;
                    }
                }
                
                if ($isSuccess) {
                    $row['parsed_lead_id'] = $parsedLeadId;
                    $row['response_preview'] = substr($response, 0, 200);
                    $mismatches[] = $row;
                }
            }
        }
        
        $totalMismatches = count($mismatches);
        
        // Display statistics
        echo "<div class='stats-grid'>";
        echo "<div class='stat-card'>";
        echo "<h3>$totalMismatches</h3>";
        echo "<p>Mismatched Records</p>";
        echo "</div>";
        
        // Count total errors
        $errorCountResult = $mysqli->query("SELECT COUNT(*) as cnt FROM lead_buffer WHERE boberdoo_status IN ('error', 'failed')");
        $errorCount = $errorCountResult->fetch_assoc()['cnt'];
        
        echo "<div class='stat-card'>";
        echo "<h3>$errorCount</h3>";
        echo "<p>Total Error Records</p>";
        echo "</div>";
        
        $percentMismatched = $errorCount > 0 ? round(($totalMismatches / $errorCount) * 100, 1) : 0;
        echo "<div class='stat-card'>";
        echo "<h3>$percentMismatched%</h3>";
        echo "<p>Are Mismatched</p>";
        echo "</div>";
        echo "</div>";
        
        if ($totalMismatches > 0) {
            echo "<div class='alert alert-warning'>";
            echo "<strong>⚠️ Found $totalMismatches leads with successful Boberdoo responses marked as errors!</strong><br>";
            echo "These leads were successfully processed by Boberdoo but incorrectly marked as failed in our system.";
            echo "</div>";
            
            // Display sample mismatches
            echo "<h3>Sample Mismatched Records (showing first 10)</h3>";
            echo "<table>";
            echo "<thead>";
            echo "<tr>";
            echo "<th>Lead ID</th>";
            echo "<th>Email</th>";
            echo "<th>Current Status</th>";
            echo "<th>Boberdoo Lead ID</th>";
            echo "<th>Response Preview</th>";
            echo "<th>Created</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            
            $displayCount = min(10, $totalMismatches);
            for ($i = 0; $i < $displayCount; $i++) {
                $lead = $mismatches[$i];
                echo "<tr>";
                echo "<td><small>{$lead['lead_id']}</small></td>";
                echo "<td>{$lead['email']}</td>";
                echo "<td class='status-error'>{$lead['boberdoo_status']}</td>";
                echo "<td>" . ($lead['parsed_lead_id'] ?? $lead['boberdoo_lead_id'] ?? '-') . "</td>";
                echo "<td><div class='xml-response'>" . htmlspecialchars($lead['response_preview']) . "...</div></td>";
                echo "<td>" . date('m/d H:i', strtotime($lead['created_at'])) . "</td>";
                echo "</tr>";
            }
            
            echo "</tbody>";
            echo "</table>";
        } else {
            echo "<div class='alert alert-success'>";
            echo "<strong>✅ No status mismatches found!</strong><br>";
            echo "All leads with successful Boberdoo responses are correctly marked.";
            echo "</div>";
        }
        
        // Step 2: Fix the data
        if ($totalMismatches > 0) {
            echo "<h2>Step 2: Fix Mismatched Records</h2>";
            
            if (isset($_POST['action'])) {
                if ($_POST['action'] === 'fix_all') {
                    $fixedCount = 0;
                    $errors = [];
                    
                    foreach ($mismatches as $lead) {
                        $updateQuery = "
                            UPDATE lead_buffer 
                            SET 
                                boberdoo_status = 'success',
                                boberdoo_lead_id = ?,
                                updated_at = NOW()
                            WHERE id = ?
                        ";
                        
                        $stmt = $mysqli->prepare($updateQuery);
                        $leadId = $lead['parsed_lead_id'] ?? $lead['boberdoo_lead_id'];
                        $stmt->bind_param("si", $leadId, $lead['id']);
                        
                        if ($stmt->execute()) {
                            $fixedCount++;
                        } else {
                            $errors[] = "Failed to fix lead {$lead['lead_id']}: " . $stmt->error;
                        }
                        $stmt->close();
                    }
                    
                    echo "<div class='alert alert-success'>";
                    echo "<strong>✅ Fixed $fixedCount out of $totalMismatches records!</strong>";
                    if (!empty($errors)) {
                        echo "<br><br>Errors encountered:<br>";
                        foreach ($errors as $error) {
                            echo "• $error<br>";
                        }
                    }
                    echo "</div>";
                    
                    // Log the fix
                    $logQuery = "
                        INSERT INTO admin_activity_log 
                        (user, action, details, ip_address, created_at) 
                        VALUES (?, ?, ?, ?, NOW())
                    ";
                    $stmt = $mysqli->prepare($logQuery);
                    $user = $_SESSION['admin_user'] ?? 'System';
                    $action = 'fix_status_mismatch';
                    $details = "Fixed $fixedCount leads with status mismatch";
                    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                    $stmt->bind_param("ssss", $user, $action, $details, $ip);
                    $stmt->execute();
                    $stmt->close();
                    
                } elseif ($_POST['action'] === 'fix_future') {
                    // This would update the form processing logic
                    echo "<div class='alert alert-info'>";
                    echo "<strong>ℹ️ To prevent future mismatches:</strong><br>";
                    echo "1. Update your form processing code to properly parse Boberdoo XML responses<br>";
                    echo "2. Check for both &lt;status&gt;Success&lt;/status&gt; and &lt;lead_id&gt; tags<br>";
                    echo "3. Only mark as 'error' if status is explicitly not 'Success'";
                    echo "</div>";
                }
            } else {
                // Show fix options
                echo "<form method='POST'>";
                echo "<p>Choose an action to fix the mismatched records:</p>";
                echo "<button type='submit' name='action' value='fix_all' class='btn btn-success' ";
                echo "onclick='return confirm(\"This will update $totalMismatches records. Continue?\")'>"; 
                echo "🔧 Fix All Mismatched Records</button>";
                echo "<button type='submit' name='action' value='fix_future' class='btn'>"; 
                echo "📋 Show Prevention Steps</button>";
                echo "</form>";
            }
        }
        
        // Step 3: Analyze root cause
        echo "<h2>Step 3: Root Cause Analysis</h2>";
        
        // Check for pattern in form_data_json
        $sampleQuery = "
            SELECT 
                form_data_json,
                boberdoo_response
            FROM lead_buffer
            WHERE boberdoo_response IS NOT NULL
            LIMIT 5
        ";
        
        $sampleResult = $mysqli->query($sampleQuery);
        $hasFormData = false;
        
        if ($sampleResult && $sampleResult->num_rows > 0) {
            echo "<div class='alert alert-info'>";
            echo "<strong>📊 Sample Response Patterns:</strong><br><br>";
            
            while ($row = $sampleResult->fetch_assoc()) {
                if ($row['form_data_json']) {
                    $hasFormData = true;
                }
                
                echo "<strong>Response Type:</strong> ";
                if (strpos($row['boberdoo_response'], '<?xml') !== false) {
                    echo "XML Format<br>";
                    
                    // Parse and display key elements
                    if (preg_match('/<status>(.*?)<\/status>/i', $row['boberdoo_response'], $matches)) {
                        echo "• Status: " . htmlspecialchars($matches[1]) . "<br>";
                    }
                    if (preg_match('/<lead_id>(.*?)<\/lead_id>/i', $row['boberdoo_response'], $matches)) {
                        echo "• Lead ID: " . htmlspecialchars($matches[1]) . "<br>";
                    }
                } else {
                    echo "JSON or Other Format<br>";
                }
                echo "<br>";
            }
            echo "</div>";
        }
        
        // Provide recommendations
        echo "<h2>📌 Recommendations</h2>";
        echo "<div class='alert alert-info'>";
        echo "<ol>";
        echo "<li><strong>Immediate:</strong> Fix the $totalMismatches mismatched records using the button above</li>";
        echo "<li><strong>Short-term:</strong> Review and update the form processing code to properly parse XML responses</li>";
        echo "<li><strong>Long-term:</strong> Implement response validation and logging to catch parsing errors</li>";
        echo "</ol>";
        echo "</div>";
        
        $mysqli->close();
        ?>
    </div>
</body>
</html>