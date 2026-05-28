<?php
/**
 * Server File Diagnostic Tool
 * Checks what's actually in your files on the server
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Server File Diagnostic</title>
    <style>
        body { font-family: Arial; max-width: 1200px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        h2 { color: #2196F3; border-bottom: 2px solid #2196F3; padding-bottom: 10px; }
        .success { color: #4CAF50; font-weight: bold; }
        .error { color: #f44336; font-weight: bold; }
        .warning { color: #ff9800; font-weight: bold; }
        pre { background: #282c34; color: #abb2bf; padding: 15px; border-radius: 8px; overflow-x: auto; max-height: 400px; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #2196F3; color: white; padding: 10px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        .highlight { background: #fff3cd; padding: 2px 4px; }
    </style>
</head>
<body>
    <h1>🔍 Server File Diagnostic Tool</h1>
    <p>Checking what's actually in your server files...</p>";

$currentDir = __DIR__;

// 1. Check blacklist.php
echo "<div class='box'>
        <h2>1. Checking blacklist.php</h2>";

$blacklistFile = $currentDir . '/blacklist.php';
if (file_exists($blacklistFile)) {
    echo "<p class='success'>✅ File exists: <code>$blacklistFile</code></p>";
    
    $content = file_get_contents($blacklistFile);
    $lines = explode("\n", $content);
    
    echo "<h3>Lines 1-25 of blacklist.php:</h3>";
    echo "<pre>";
    for ($i = 0; $i < min(25, count($lines)); $i++) {
        $lineNum = $i + 1;
        $line = htmlspecialchars($lines[$i]);
        
        // Highlight line 17 and lines with 'config'
        if ($lineNum == 17 || stripos($line, 'config') !== false) {
            echo "<span class='highlight'>$lineNum: $line</span>\n";
        } else {
            echo "$lineNum: $line\n";
        }
    }
    echo "</pre>";
    
    // Find the config include line
    $configLine = '';
    foreach ($lines as $num => $line) {
        if (stripos($line, 'config.php') !== false && stripos($line, 'require') !== false) {
            $configLine = trim($line);
            echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>
                    <strong>Config include found on line " . ($num + 1) . ":</strong><br>
                    <code>$configLine</code>
                  </div>";
            break;
        }
    }
} else {
    echo "<p class='error'>❌ File NOT found: <code>$blacklistFile</code></p>";
}

echo "</div>";

// 2. Check includes/config.php
echo "<div class='box'>
        <h2>2. Checking includes/config.php</h2>";

$configFile = $currentDir . '/includes/config.php';
if (file_exists($configFile)) {
    echo "<p class='success'>✅ File exists: <code>$configFile</code></p>";
    
    $content = file_get_contents($configFile);
    $lines = explode("\n", $content);
    
    echo "<h3>First 50 lines of config.php:</h3>";
    echo "<pre>";
    for ($i = 0; $i < min(50, count($lines)); $i++) {
        $lineNum = $i + 1;
        $line = htmlspecialchars($lines[$i]);
        
        // Highlight lines with 'require' or 'db-config'
        if (stripos($line, 'require') !== false || stripos($line, 'db-config') !== false) {
            echo "<span class='highlight'>$lineNum: $line</span>\n";
        } else {
            echo "$lineNum: $line\n";
        }
    }
    echo "</pre>";
    
    // Find the db-config require line
    foreach ($lines as $num => $line) {
        if (stripos($line, 'db-config.php') !== false && stripos($line, 'require') !== false) {
            $dbConfigLine = trim($line);
            echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>
                    <strong>DB Config require found on line " . ($num + 1) . ":</strong><br>
                    <code>$dbConfigLine</code>
                  </div>";
            
            // Check what this path actually resolves to
            $pattern = '/[\'"](.+?db-config\.php)[\'"]/';
            if (preg_match($pattern, $line, $matches)) {
                $rawPath = $matches[1];
                echo "<p>Raw path in code: <code>$rawPath</code></p>";
                
                // Try to resolve it
                $basePath = dirname($configFile);
                echo "<p>Base path (config.php directory): <code>$basePath</code></p>";
                
                if (strpos($rawPath, '__DIR__') !== false) {
                    echo "<p>Path uses __DIR__, will be resolved at runtime</p>";
                }
            }
            break;
        }
    }
} else {
    echo "<p class='error'>❌ File NOT found: <code>$configFile</code></p>";
    echo "<p class='warning'>⚠️ This is your problem! The config.php file doesn't exist where it should be.</p>";
}

echo "</div>";

// 3. Check if db-config.php exists
echo "<div class='box'>
        <h2>3. Checking db-config.php location</h2>";

$possiblePaths = [
    dirname(dirname($currentDir)) . '/inc/config/db-config.php',
    dirname(dirname(dirname($currentDir))) . '/inc/config/db-config.php',
    dirname($currentDir) . '/inc/config/db-config.php',
    dirname($currentDir) . '/config/db-config.php',
    '/home/healthcareins/public_html/multi-quote/inc/config/db-config.php'
];

$foundPath = null;
echo "<table>";
echo "<tr><th>Possible Path</th><th>Exists?</th><th>Readable?</th></tr>";

foreach ($possiblePaths as $path) {
    $exists = file_exists($path);
    $readable = $exists ? is_readable($path) : false;
    
    echo "<tr>";
    echo "<td><code>$path</code></td>";
    echo "<td>" . ($exists ? "<span class='success'>✅ Yes</span>" : "<span class='error'>❌ No</span>") . "</td>";
    echo "<td>" . ($readable ? "<span class='success'>✅ Yes</span>" : "<span class='error'>❌ No</span>") . "</td>";
    echo "</tr>";
    
    if ($exists && !$foundPath) {
        $foundPath = $path;
    }
}

echo "</table>";

if ($foundPath) {
    echo "<p class='success'>✅ Found db-config.php at: <code>$foundPath</code></p>";
    
    // Calculate the correct relative path from config.php to db-config.php
    $configDir = dirname($configFile);
    $relativePath = str_replace($configDir, '', $foundPath);
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>
            <strong>Correct path for config.php:</strong><br>
            <code>require_once __DIR__ . '$relativePath';</code>
          </div>";
} else {
    echo "<p class='error'>❌ db-config.php not found in any expected location!</p>";
}

echo "</div>";

// 4. Test the actual path resolution
echo "<div class='box'>
        <h2>4. Path Resolution Test</h2>";

echo "<table>";
echo "<tr><th>Variable</th><th>Value</th></tr>";
echo "<tr><td>Current script (__FILE__)</td><td><code>" . __FILE__ . "</code></td></tr>";
echo "<tr><td>Current directory (__DIR__)</td><td><code>" . __DIR__ . "</code></td></tr>";
echo "<tr><td>Config file location</td><td><code>$configFile</code></td></tr>";

if (file_exists($configFile)) {
    $configDir = dirname($configFile);
    echo "<tr><td>Config directory (dirname)</td><td><code>$configDir</code></td></tr>";
    echo "<tr><td>Up one level</td><td><code>" . dirname($configDir) . "</code></td></tr>";
    echo "<tr><td>Up two levels</td><td><code>" . dirname(dirname($configDir)) . "</code></td></tr>";
    
    $testPath = dirname(dirname($configDir)) . '/inc/config/db-config.php';
    echo "<tr><td>Test path (__DIR__/../../inc/config/db-config.php)</td><td><code>$testPath</code></td></tr>";
    echo "<tr><td>Test path exists?</td><td>";
    if (file_exists($testPath)) {
        echo "<span class='success'>✅ YES</span>";
    } else {
        echo "<span class='error'>❌ NO</span>";
    }
    echo "</td></tr>";
}

echo "</table>";

echo "</div>";

// 5. Solution
echo "<div class='box'>
        <h2>🔧 Solution</h2>";

if (!file_exists($configFile)) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; border-left: 4px solid #f44336;'>
            <h3>Problem: includes/config.php is missing!</h3>
            <p><strong>Solution:</strong></p>
            <ol>
                <li>Create the folder: <code>{$currentDir}/includes/</code></li>
                <li>Upload config.php to that folder</li>
                <li>Refresh this page to verify</li>
            </ol>
          </div>";
} else if ($foundPath) {
    // Calculate what the require line should be
    $configDir = dirname($configFile);
    
    // Get relative path
    $commonPath = $currentDir;
    $up = 0;
    
    // Count how many levels up we need to go
    $temp = $configDir;
    while (strpos($foundPath, $temp) === false && $up < 5) {
        $temp = dirname($temp);
        $up++;
    }
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #4CAF50;'>
            <h3>✅ All files exist!</h3>
            <p>But make sure your config.php has the correct path:</p>
            <code>require_once __DIR__ . '/../../inc/config/db-config.php';</code>
            
            <p style='margin-top: 15px;'><strong>Or use absolute path:</strong></p>
            <code>require_once '$foundPath';</code>
          </div>";
}

echo "</div>";

echo "</body></html>";
?>