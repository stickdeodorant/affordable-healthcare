<?php
require_once __DIR__ . '/../inc/feature-flags.php';
if (empty($featureFlags['enable_legacy_pages'])) {
    http_response_code(404);
    exit;
}
/**
 * Comprehensive Lead Check - Shows ALL recent leads
 * Upload to /multi-quote/ and run
 */

require_once __DIR__ . '/inc/config/db-config.php';

$mysqli = getDBConnection();
if (!$mysqli) {
    die("Database connection failed\n");
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>All Recent Leads Check</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #4CAF50; color: white; }
        .pending { background: #fff3cd; }
        .success { background: #d4edda; }
        .error { background: #f8d7da; }
        .section { background: #f0f0f0; padding: 15px; margin: 20px 0; border-radius: 5px; }
        pre { background: white; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Lead System Diagnostic</h1>";

// 1. Check if we're connected to the right database
echo "<div class='section'>";
echo "<h2>1. Database Check:</h2>";
$result = $mysqli->query("SELECT DATABASE() as db");
$row = $result->fetch_assoc();
echo "<strong>Connected to database:</strong> " . $row['db'] . "<br>";

// Check table exists
$result = $mysqli->query("SHOW TABLES LIKE 'lead_buffer'");
echo "<strong>lead_buffer table exists:</strong> " . ($result->num_rows > 0 ? "YES" : "NO") . "<br>";
echo "</div>";

// 2. Show last 20 leads
echo "<div class='section'>";
echo "<h2>2. Last 20 Leads in lead_buffer:</h2>";

$query = "
    SELECT 
        lb.id,
        lb.lead_id,
        lb.email,
        lb.first_name,
        lb.last_name,
        lb.boberdoo_status,
        lb.boberdoo_response,
        lb.boberdoo_lead_id,
        lb.created_at,
        lb.updated_at,
        arl.response_body as api_log_response
    FROM lead_buffer lb
    LEFT JOIN api_response_log arl ON lb.lead_id = arl.lead_id
    ORDER BY lb.id DESC
    LIMIT 20
";

$result = $mysqli->query($query);

if ($result && $result->num_rows > 0) {
    echo "<table>";
    echo "<tr>
            <th>ID</th>
            <th>Lead ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Status</th>
            <th>Response?</th>
            <th>API Log?</th>
            <th>Created</th>
            <th>Updated</th>
          </tr>";
    
    while ($row = $result->fetch_assoc()) {
        $statusClass = '';
        if ($row['boberdoo_status'] == 'pending') $statusClass = 'pending';
        elseif ($row['boberdoo_status'] == 'success') $statusClass = 'success';
        else $statusClass = 'error';
        
        echo "<tr class='$statusClass'>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . substr($row['lead_id'], 0, 30) . "...</td>";
        echo "<td>{$row['first_name']} {$row['last_name']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td><strong>{$row['boberdoo_status']}</strong></td>";
        echo "<td>" . (!empty($row['boberdoo_response']) ? "✓" : "✗") . "</td>";
        echo "<td>" . (!empty($row['api_log_response']) ? "✓" : "✗") . "</td>";
        echo "<td>" . date('m/d H:i', strtotime($row['created_at'])) . "</td>";
        echo "<td>" . ($row['updated_at'] ? date('m/d H:i', strtotime($row['updated_at'])) : 'Never') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No leads found in lead_buffer table!</p>";
}
echo "</div>";

// 3. Look for specific lead by ID
if (isset($_GET['lead_id'])) {
    echo "<div class='section'>";
    echo "<h2>3. Specific Lead Details:</h2>";
    
    $stmt = $mysqli->prepare("
        SELECT * FROM lead_buffer 
        WHERE lead_id LIKE ? 
        OR email LIKE ?
        ORDER BY id DESC 
        LIMIT 1
    ");
    
    $searchTerm = '%' . $_GET['lead_id'] . '%';
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo "<pre>";
        print_r($row);
        echo "</pre>";
    } else {
        echo "<p>Lead not found with search term: " . htmlspecialchars($_GET['lead_id']) . "</p>";
    }
    $stmt->close();
    echo "</div>";
}

// 4. Search for test leads
echo "<div class='section'>";
echo "<h2>4. Search for Test Leads:</h2>";
echo "<form method='GET'>";
echo "<input type='text' name='lead_id' placeholder='Enter lead ID, email, or name' size='50'>";
echo "<button type='submit'>Search</button>";
echo "</form>";

// Look for common test patterns
$testPatterns = [
    'infinixmedia',
    'test',
    'cruby',
    'christopher'
];

echo "<h3>Searching for test patterns:</h3>";
foreach ($testPatterns as $pattern) {
    $stmt = $mysqli->prepare("
        SELECT id, lead_id, email, boberdoo_status, created_at 
        FROM lead_buffer 
        WHERE email LIKE ? 
        OR first_name LIKE ? 
        OR last_name LIKE ?
        ORDER BY id DESC 
        LIMIT 5
    ");
    
    $searchPattern = '%' . $pattern . '%';
    $stmt->bind_param("sss", $searchPattern, $searchPattern, $searchPattern);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<strong>Found for '$pattern':</strong><br>";
        while ($row = $result->fetch_assoc()) {
            echo "- ID: {$row['id']}, Email: {$row['email']}, Status: {$row['boberdoo_status']}, Created: {$row['created_at']}<br>";
        }
    } else {
        echo "<strong>No results for '$pattern'</strong><br>";
    }
    $stmt->close();
}
echo "</div>";

// 5. Check form processing file
echo "<div class='section'>";
echo "<h2>5. Form Processing File Check:</h2>";

$formPath = __DIR__ . '/inc/form-processing.php';
if (file_exists($formPath)) {
    $content = file_get_contents($formPath);
    $fileSize = filesize($formPath);
    $lastModified = date('Y-m-d H:i:s', filemtime($formPath));
    
    echo "<strong>File exists:</strong> YES<br>";
    echo "<strong>File size:</strong> " . number_format($fileSize) . " bytes<br>";
    echo "<strong>Last modified:</strong> $lastModified<br>";
    
    // Look for critical code sections
    echo "<h3>Code presence check:</h3>";
    $checks = [
        'INSERT INTO lead_buffer' => 'Initial lead save',
        'UPDATE lead_buffer' => 'Status update code',
        '\$bufferId = \$mysqli->insert_id' => 'Captures buffer ID',
        'if \(\$bufferId\)' => 'Buffer ID check',
        'boberdoo_status' => 'Status field reference',
        'EMERGENCY FIX' => 'Emergency fix added'
    ];
    
    foreach ($checks as $pattern => $description) {
        $found = preg_match('/' . $pattern . '/i', $content);
        echo "- $description: " . ($found ? "<span style='color:green'>✓ FOUND</span>" : "<span style='color:red'>✗ NOT FOUND</span>") . "<br>";
    }
} else {
    echo "<strong style='color:red'>form-processing.php NOT FOUND!</strong><br>";
}
echo "</div>";

// 6. Count status distribution
echo "<div class='section'>";
echo "<h2>6. Status Distribution (Last 7 Days):</h2>";

$query = "
    SELECT 
        boberdoo_status,
        COUNT(*) as count,
        DATE(created_at) as date
    FROM lead_buffer
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY boberdoo_status, DATE(created_at)
    ORDER BY date DESC, boberdoo_status
";

$result = $mysqli->query($query);

if ($result) {
    echo "<table>";
    echo "<tr><th>Date</th><th>Status</th><th>Count</th></tr>";
    
    $lastDate = '';
    while ($row = $result->fetch_assoc()) {
        if ($lastDate != $row['date']) {
            if ($lastDate != '') echo "<tr><td colspan='3' style='background:#ddd;height:2px;'></td></tr>";
            $lastDate = $row['date'];
        }
        
        $statusClass = '';
        if ($row['boberdoo_status'] == 'pending') $statusClass = 'pending';
        elseif ($row['boberdoo_status'] == 'success') $statusClass = 'success';
        
        echo "<tr class='$statusClass'>";
        echo "<td>{$row['date']}</td>";
        echo "<td>{$row['boberdoo_status']}</td>";
        echo "<td>{$row['count']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}
echo "</div>";

// 7. Quick fix for pending leads
echo "<div class='section'>";
echo "<h2>7. Quick Fix Options:</h2>";

$pendingCount = $mysqli->query("SELECT COUNT(*) as count FROM lead_buffer WHERE boberdoo_status = 'pending'")->fetch_assoc()['count'];

echo "<p>Total pending leads: <strong>$pendingCount</strong></p>";

if ($pendingCount > 0) {
    echo "<form method='POST'>";
    echo "<button type='submit' name='fix_all' value='1' onclick='return confirm(\"Fix all $pendingCount pending leads?\")'>Fix All Pending Leads</button>";
    echo "</form>";
    
    if (isset($_POST['fix_all'])) {
        // Fix all pending leads that have API responses
        $fixQuery = "
            UPDATE lead_buffer lb
            INNER JOIN api_response_log arl ON lb.lead_id = arl.lead_id
            SET lb.boberdoo_status = 'success',
                lb.boberdoo_response = arl.response_body,
                lb.boberdoo_lead_id = SUBSTRING_INDEX(SUBSTRING_INDEX(arl.response_body, '<lead_id>', -1), '</lead_id>', 1),
                lb.updated_at = NOW()
            WHERE lb.boberdoo_status = 'pending'
            AND arl.response_body LIKE '%<status>Success</status>%'
        ";
        
        $mysqli->query($fixQuery);
        $fixed = $mysqli->affected_rows;
        
        echo "<p style='color:green;'><strong>✓ Fixed $fixed leads!</strong></p>";
        echo "<script>setTimeout(function() { location.reload(); }, 2000);</script>";
    }
}
echo "</div>";

echo "</body></html>";

$mysqli->close();
?>