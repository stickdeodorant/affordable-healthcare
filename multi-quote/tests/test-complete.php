<?php
require_once __DIR__ . '/../../inc/feature-flags.php';
if (empty($featureFlags['enable_legacy_pages'])) {
    http_response_code(404);
    exit;
}
/**
 * Lead Management System - Complete Test Suite
 * Location: /multi-quote/test-complete.php
 * 
 * This file tests ALL components of your lead management system
 */

session_start();

// Include configuration
require_once __DIR__ . '/inc/config/db-config.php';

// Get database connection
$mysqli = getDBConnection();

if (!$mysqli) {
    die("❌ Database connection failed. Check db-config.php");
}

// Test results array
$results = [];
$totalTests = 0;
$passedTests = 0;
$failedTests = [];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lead System - Complete Test Suite</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #2196F3;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .test-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .test-section h2 {
            color: #2196F3;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .test-item {
            display: flex;
            align-items: center;
            padding: 12px;
            margin: 8px 0;
            background: #f9f9f9;
            border-radius: 5px;
            border-left: 4px solid #ddd;
        }
        .test-item.pass {
            border-left-color: #4CAF50;
            background: #f1f8f4;
        }
        .test-item.fail {
            border-left-color: #f44336;
            background: #fef1f0;
        }
        .test-item.warning {
            border-left-color: #ff9800;
            background: #fff8e1;
        }
        .status {
            font-size: 24px;
            margin-right: 15px;
        }
        .test-name {
            flex: 1;
            font-weight: 600;
            color: #333;
        }
        .test-result {
            color: #666;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 5px;
            background: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #1976D2;
        }
        .btn-success {
            background: #4CAF50;
        }
        .btn-success:hover {
            background: #388E3C;
        }
        .btn-danger {
            background: #f44336;
        }
        .btn-danger:hover {
            background: #d32f2f;
        }
        .summary {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .summary h3 {
            color: #1976D2;
            margin-bottom: 10px;
        }
        .code {
            background: #263238;
            color: #aed581;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th {
            background: #2196F3;
            color: white;
            padding: 12px;
            text-align: left;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .progress-bar {
            width: 100%;
            height: 30px;
            background: #e0e0e0;
            border-radius: 15px;
            overflow: hidden;
            margin: 20px 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4CAF50 0%, #8BC34A 100%);
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .alert-info {
            background: #e3f2fd;
            color: #1565c0;
            border: 1px solid #90caf9;
        }
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔬 Lead Management System - Complete Test Suite</h1>
            <p>Testing all components of your multi-quote lead management system</p>
            <p><strong>System Path:</strong> /multi-quote/</p>
            <p><strong>Database:</strong> <?= DB_NAME ?></p>
            <p><strong>Test Time:</strong> <?= date('Y-m-d H:i:s') ?></p>
        </div>

        <!-- Test 1: Database & Tables -->
        <div class="test-section">
            <h2>1️⃣ Database & Table Structure</h2>
            
            <?php
            // Test database connection
            $test = $mysqli->ping();
            $totalTests++;
            if ($test) {
                $passedTests++;
                echo '<div class="test-item pass">
                        <span class="status">✅</span>
                        <span class="test-name">Database Connection</span>
                        <span class="test-result">Connected to ' . DB_NAME . '</span>
                      </div>';
            } else {
                $failedTests[] = 'Database Connection';
                echo '<div class="test-item fail">
                        <span class="status">❌</span>
                        <span class="test-name">Database Connection</span>
                        <span class="test-result">Failed to connect</span>
                      </div>';
            }
            
            // Test required tables
            $requiredTables = [
                'lead_buffer' => 'Temporary lead storage (7-day)',
                'lead_history' => 'Permanent lead archive',
                'dashboard_stats' => 'Dashboard statistics',
                'email_blacklist' => 'Email/phone blacklist',
                'resubmission_queue' => 'Lead resubmission queue',
                'api_response_log' => 'API response logging',
                'system_health_log' => 'System health monitoring',
                'admin_activity_log' => 'Admin activity tracking'
            ];
            
            echo '<table>
                    <tr>
                        <th>Table</th>
                        <th>Purpose</th>
                        <th>Status</th>
                        <th>Records</th>
                    </tr>';
            
            foreach ($requiredTables as $table => $purpose) {
                $totalTests++;
                $result = $mysqli->query("SHOW TABLES LIKE '$table'");
                
                if ($result && $result->num_rows > 0) {
                    $countResult = $mysqli->query("SELECT COUNT(*) as cnt FROM $table");
                    $count = $countResult ? $countResult->fetch_assoc()['cnt'] : 'Error';
                    $passedTests++;
                    
                    echo "<tr>
                            <td><strong>$table</strong></td>
                            <td>$purpose</td>
                            <td><span style='color: #4CAF50'>✅ Exists</span></td>
                            <td>$count records</td>
                          </tr>";
                } else {
                    $failedTests[] = "Table: $table";
                    echo "<tr>
                            <td><strong>$table</strong></td>
                            <td>$purpose</td>
                            <td><span style='color: #f44336'>❌ Missing</span></td>
                            <td>-</td>
                          </tr>";
                }
            }
            echo '</table>';
            
            // Test primary_phone column fix
            $totalTests++;
            $colResult = $mysqli->query("SHOW COLUMNS FROM lead_buffer WHERE Field = 'primary_phone'");
            if ($colResult && $row = $colResult->fetch_assoc()) {
                if ($row['Null'] == 'YES' || $row['Default'] !== null) {
                    $passedTests++;
                    echo '<div class="test-item pass">
                            <span class="status">✅</span>
                            <span class="test-name">Primary Phone Column</span>
                            <span class="test-result">Properly configured (nullable or has default)</span>
                          </div>';
                } else {
                    $failedTests[] = 'Primary Phone Column Configuration';
                    echo '<div class="test-item fail">
                            <span class="status">❌</span>
                            <span class="test-name">Primary Phone Column</span>
                            <span class="test-result">NOT NULL with no default - needs fix!</span>
                          </div>';
                }
            }
            ?>
        </div>

        <!-- Test 2: File Structure -->
        <div class="test-section">
            <h2>2️⃣ File Structure & Configuration</h2>
            
            <?php
            $criticalFiles = [
                '/inc/form-processing.php' => 'Form processor',
                '/inc/config/db-config.php' => 'Database configuration',
                '/inc/cron-jobs.php' => 'Cron job handler',
                '/inc/resubmit-handler.php' => 'Lead resubmission',
                '/inc/admin-panel.php' => 'Admin dashboard',
                '/inc/classes/SessionManager.php' => 'Session management',
                '/inc/classes/SecurityHelper.php' => 'Security functions',
                '/js/custom.js' => 'Form JavaScript',
                '/inc/api/get-stats.php' => 'Statistics API',
                '/inc/api/get-recent.php' => 'Recent leads API'
            ];
            
            foreach ($criticalFiles as $file => $description) {
                $totalTests++;
                $filePath = dirname(__FILE__) . $file;
                
                if (file_exists($filePath)) {
                    $passedTests++;
                    $fileSize = filesize($filePath);
                    $sizeKB = round($fileSize / 1024, 2);
                    
                    echo '<div class="test-item pass">
                            <span class="status">✅</span>
                            <span class="test-name">' . $file . '</span>
                            <span class="test-result">' . $description . ' (' . $sizeKB . ' KB)</span>
                          </div>';
                } else {
                    $failedTests[] = "File: $file";
                    echo '<div class="test-item fail">
                            <span class="status">❌</span>
                            <span class="test-name">' . $file . '</span>
                            <span class="test-result">Missing - ' . $description . '</span>
                          </div>';
                }
            }
            ?>
        </div>

        <!-- Test 3: Create Test Lead -->
        <div class="test-section">
            <h2>3️⃣ Lead Creation Test</h2>
            
            <?php if (isset($_GET['create_lead'])): ?>
                <?php
                $totalTests++;
                
                // Generate test data
                $testData = [
                    'lead_id' => generateLeadId('TEST'),
                    'email' => 'test-' . time() . '@example.com',
                    'phone' => '555' . mt_rand(1000000, 9999999),
                    'primary_phone' => '555' . mt_rand(1000000, 9999999),
                    'first_name' => 'Test',
                    'last_name' => 'User',
                    'city' => 'TestCity',
                    'state' => 'CA',
                    'zip' => '90210',
                    'ip_address' => '127.0.0.1',
                    'boberdoo_status' => 'test',
                    'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days'))
                ];
                
                // Try to insert
                $stmt = $mysqli->prepare("
                    INSERT INTO lead_buffer (
                        lead_id, email, phone, primary_phone, first_name, last_name,
                        city, state, zip, ip_address, boberdoo_status, expires_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                if ($stmt) {
                    $stmt->bind_param("ssssssssssss",
                        $testData['lead_id'],
                        $testData['email'],
                        $testData['phone'],
                        $testData['primary_phone'],
                        $testData['first_name'],
                        $testData['last_name'],
                        $testData['city'],
                        $testData['state'],
                        $testData['zip'],
                        $testData['ip_address'],
                        $testData['boberdoo_status'],
                        $testData['expires_at']
                    );
                    
                    if ($stmt->execute()) {
                        $insertId = $mysqli->insert_id;
                        $passedTests++;
                        
                        echo '<div class="alert alert-success">
                                ✅ Test lead created successfully!<br>
                                <strong>Insert ID:</strong> ' . $insertId . '<br>
                                <strong>Lead ID:</strong> ' . $testData['lead_id'] . '<br>
                                <strong>Email:</strong> ' . $testData['email'] . '<br>
                                <strong>Phone:</strong> ' . $testData['phone'] . '
                              </div>';
                        
                        // Verify the lead
                        $verify = $mysqli->query("SELECT * FROM lead_buffer WHERE id = $insertId");
                        if ($verify && $verify->num_rows > 0) {
                            $lead = $verify->fetch_assoc();
                            echo '<div class="test-item pass">
                                    <span class="status">✅</span>
                                    <span class="test-name">Lead Verification</span>
                                    <span class="test-result">Lead found in buffer with status: ' . $lead['boberdoo_status'] . '</span>
                                  </div>';
                        }
                        
                        $stmt->close();
                    } else {
                        $failedTests[] = 'Lead Creation';
                        echo '<div class="alert alert-error">
                                ❌ Failed to create test lead: ' . $stmt->error . '
                              </div>';
                    }
                } else {
                    $failedTests[] = 'Lead Creation Prepare';
                    echo '<div class="alert alert-error">
                            ❌ Failed to prepare statement: ' . $mysqli->error . '
                          </div>';
                }
                ?>
            <?php else: ?>
                <p>Click the button below to test lead creation:</p>
                <a href="?create_lead=1" class="btn btn-success">🚀 Create Test Lead</a>
            <?php endif; ?>
        </div>

        <!-- Test 4: API Endpoints -->
        <div class="test-section">
            <h2>4️⃣ API Endpoint Tests</h2>
            
            <div id="api-tests">
                <p>Testing API endpoints...</p>
            </div>
            
            <script>
                // Test API endpoints via AJAX
                const apiTests = [
                    { url: 'inc/api/get-stats.php', name: 'Statistics API' },
                    { url: 'inc/api/get-recent.php', name: 'Recent Leads API' },
                    { url: 'inc/api/get-buffer.php', name: 'Buffer API' },
                    { url: 'inc/api/system-check.php', name: 'System Check API' }
                ];
                
                const apiContainer = document.getElementById('api-tests');
                apiContainer.innerHTML = '';
                
                apiTests.forEach(test => {
                    fetch(test.url)
                        .then(response => {
                            const status = response.ok ? 'pass' : 'fail';
                            const icon = response.ok ? '✅' : '❌';
                            
                            apiContainer.innerHTML += `
                                <div class="test-item ${status}">
                                    <span class="status">${icon}</span>
                                    <span class="test-name">${test.name}</span>
                                    <span class="test-result">Status: ${response.status}</span>
                                </div>
                            `;
                            
                            return response.json();
                        })
                        .then(data => {
                            console.log(`${test.name} response:`, data);
                        })
                        .catch(error => {
                            apiContainer.innerHTML += `
                                <div class="test-item fail">
                                    <span class="status">❌</span>
                                    <span class="test-name">${test.name}</span>
                                    <span class="test-result">Error: ${error.message}</span>
                                </div>
                            `;
                        });
                });
            </script>
        </div>

        <!-- Test 5: Form Submission Flow -->
        <div class="test-section">
            <h2>5️⃣ Form Submission Test</h2>
            
            <?php if (isset($_GET['test_form'])): ?>
                <div class="alert alert-info">
                    Testing form submission to form-processing.php...
                </div>
                
                <form id="test-form" method="POST" action="inc/form-processing.php">
                    <!-- Generate CSRF token -->
                    <input type="hidden" name="csrf_token" value="<?= bin2hex(random_bytes(32)) ?>">
                    
                    <!-- Test data -->
                    <input type="hidden" name="First_Name" value="FormTest">
                    <input type="hidden" name="Last_Name" value="User">
                    <input type="hidden" name="Email" value="formtest-<?= time() ?>@example.com">
                    <input type="hidden" name="Primary_Phone" value="555<?= mt_rand(1000000, 9999999) ?>">
                    <input type="hidden" name="DOB" value="01/01/1980">
                    <input type="hidden" name="Gender" value="M">
                    <input type="hidden" name="Zip" value="90210">
                    <input type="hidden" name="City" value="Beverly Hills">
                    <input type="hidden" name="State" value="CA">
                    <input type="hidden" name="Age" value="44">
                    <input type="hidden" name="Household" value="2">
                    <input type="hidden" name="Household_Income" value="50000">
                    <input type="hidden" name="Currently_Insured" value="Yes">
                    <input type="hidden" name="Urgency" value="30days">
                    <input type="hidden" name="Reason" value="Testing">
                    <input type="hidden" name="SRC" value="Test">
                    <input type="hidden" name="TYPE" value="24">
                    <input type="hidden" name="Redirect_URL" value="/multi-quote/thank-you/test.php">
                </form>
                
                <script>
                    // Submit test form via AJAX
                    const testForm = document.getElementById('test-form');
                    const formData = new FormData(testForm);
                    
                    fetch('inc/form-processing.php', {
                        method: 'POST',
                        body: new URLSearchParams(formData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Form submission response:', data);
                        
                        const resultDiv = document.createElement('div');
                        if (data.success || data.redirect) {
                            resultDiv.className = 'alert alert-success';
                            resultDiv.innerHTML = '✅ Form submission successful!<br>Response: ' + JSON.stringify(data, null, 2);
                        } else {
                            resultDiv.className = 'alert alert-error';
                            resultDiv.innerHTML = '❌ Form submission failed!<br>Error: ' + (data.error || 'Unknown error');
                        }
                        testForm.parentNode.insertBefore(resultDiv, testForm.nextSibling);
                    })
                    .catch(error => {
                        const resultDiv = document.createElement('div');
                        resultDiv.className = 'alert alert-error';
                        resultDiv.innerHTML = '❌ Form submission error: ' + error.message;
                        testForm.parentNode.insertBefore(resultDiv, testForm.nextSibling);
                    });
                </script>
            <?php else: ?>
                <p>Test the complete form submission flow (without actual Boberdoo submission):</p>
                <a href="?test_form=1" class="btn">📝 Test Form Submission</a>
            <?php endif; ?>
        </div>

        <!-- Test 6: Recent Activity -->
        <div class="test-section">
            <h2>6️⃣ Recent System Activity</h2>
            
            <?php
            // Check recent leads in buffer
            $recentBuffer = $mysqli->query("
                SELECT * FROM lead_buffer 
                ORDER BY created_at DESC 
                LIMIT 5
            ");
            
            if ($recentBuffer && $recentBuffer->num_rows > 0) {
                echo '<h3>Recent Buffer Entries:</h3>';
                echo '<table>
                        <tr>
                            <th>Lead ID</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>';
                
                while ($lead = $recentBuffer->fetch_assoc()) {
                    $statusClass = $lead['boberdoo_status'] == 'success' ? 'color: #4CAF50' : 'color: #ff9800';
                    echo '<tr>
                            <td>' . htmlspecialchars($lead['lead_id']) . '</td>
                            <td>' . htmlspecialchars($lead['email']) . '</td>
                            <td style="' . $statusClass . '">' . htmlspecialchars($lead['boberdoo_status']) . '</td>
                            <td>' . $lead['created_at'] . '</td>
                          </tr>';
                }
                echo '</table>';
            } else {
                echo '<p>No recent leads in buffer.</p>';
            }
            
            // Check blacklist
            $blacklistCount = $mysqli->query("SELECT COUNT(*) as cnt FROM email_blacklist");
            $blCount = $blacklistCount->fetch_assoc()['cnt'];
            
            echo '<div class="test-item">
                    <span class="status">📊</span>
                    <span class="test-name">Blacklist Entries</span>
                    <span class="test-result">' . $blCount . ' emails/phones blacklisted</span>
                  </div>';
            ?>
        </div>

        <!-- Summary -->
        <div class="test-section">
            <div class="summary">
                <h3>📊 Test Summary</h3>
                
                <?php
                $successRate = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 1) : 0;
                ?>
                
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $successRate ?>%">
                        <?= $successRate ?>%
                    </div>
                </div>
                
                <p><strong>Total Tests:</strong> <?= $totalTests ?></p>
                <p><strong>Passed:</strong> <span style="color: #4CAF50"><?= $passedTests ?></span></p>
                <p><strong>Failed:</strong> <span style="color: #f44336"><?= count($failedTests) ?></span></p>
                
                <?php if (count($failedTests) > 0): ?>
                    <h4>Failed Tests:</h4>
                    <ul>
                        <?php foreach ($failedTests as $test): ?>
                            <li><?= htmlspecialchars($test) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                
                <?php if ($successRate == 100): ?>
                    <h3 style="color: #4CAF50; margin-top: 20px;">✅ All tests passed! Your system is fully operational.</h3>
                <?php elseif ($successRate >= 80): ?>
                    <h3 style="color: #ff9800; margin-top: 20px;">⚠️ System is mostly functional but needs attention.</h3>
                <?php else: ?>
                    <h3 style="color: #f44336; margin-top: 20px;">❌ Critical issues detected. Please fix before using.</h3>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="test-section">
            <h2>🔧 Quick Actions & Tools</h2>
            
            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                <a href="/multi-quote/primary-phone-fix.php" class="btn">🔧 Fix Primary Phone</a>
                <a href="/multi-quote/admin-panel.php" class="btn">📊 Admin Panel</a>
                <a href="/multi-quote/verify-tables.php" class="btn">✅ Verify Tables</a>
                <a href="/multi-quote/" class="btn btn-success">📝 Test Live Form</a>
                <a href="?create_lead=1" class="btn">🚀 Create Test Lead</a>
                <a href="?test_form=1" class="btn">📤 Test Submission</a>
                <a href="test-complete.php" class="btn btn-danger">🔄 Restart Tests</a>
            </div>
        </div>

        <!-- Manual Testing Guide -->
        <div class="test-section">
            <h2>📋 Manual Testing Checklist</h2>
            
            <ol>
                <li><strong>Test the Live Form:</strong>
                    <ul>
                        <li>Go to <a href="/multi-quote/">/multi-quote/</a></li>
                        <li>Fill out all form steps</li>
                        <li>Submit with test data</li>
                        <li>Verify redirect to thank you page</li>
                    </ul>
                </li>
                
                <li><strong>Check Database:</strong>
                    <div class="code">
-- Check if lead was saved to buffer
SELECT * FROM lead_buffer ORDER BY created_at DESC LIMIT 1;

-- Check if it moved to history (after success)
SELECT * FROM lead_history ORDER BY submission_timestamp DESC LIMIT 1;

-- Check API response log
SELECT * FROM api_response_log ORDER BY created_at DESC LIMIT 1;
                    </div>
                </li>
                
                <li><strong>Test Admin Panel:</strong>
                    <ul>
                        <li>Go to <a href="/multi-quote/admin-panel.php">Admin Panel</a></li>
                        <li>View recent leads</li>
                        <li>Test resubmission feature</li>
                        <li>Check blacklist management</li>
                    </ul>
                </li>
                
                <li><strong>Test Cron Jobs (Manual):</strong>
                    <div class="code">
# SSH into server and run:
php /home/[username]/public_html/multi-quote/inc/cron-jobs.php cleanup_buffer
php /home/[username]/public_html/multi-quote/inc/cron-jobs.php calculate_stats
php /home/[username]/public_html/multi-quote/inc/cron-jobs.php process_queue
                    </div>
                </li>
                
                <li><strong>Monitor Error Logs:</strong>
                    <div class="code">
# Check PHP error log
tail -f /home/[username]/public_html/error_log

# Check lead management cron log
tail -f /var/log/lead_management_cron.log
                    </div>
                </li>
            </ol>
        </div>

    </div>

    <script>
        // Auto-refresh progress
        setTimeout(function() {
            const progressElements = document.querySelectorAll('.progress-fill');
            progressElements.forEach(el => {
                el.style.transition = 'width 1s ease-in-out';
            });
        }, 100);
    </script>
</body>
</html>

<?php
// Close database connection
$mysqli->close();
?>