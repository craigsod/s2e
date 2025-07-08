<?php
/**
 * CMS Server Compatibility Diagnostic Tool
 * 
 * This script collects additional server information beyond what phpinfo() provides
 * to help identify potential compatibility issues across different hosting environments.
 * 
 * Usage: Upload to each server, access via browser, save the output.
 */

// Set appropriate headers for plain text output
header('Content-Type: text/plain');

// Disable error display but log errors
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Start output buffer to capture all output
ob_start();

echo "===== CMS SERVER COMPATIBILITY DIAGNOSTIC =====\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n\n";

// SYSTEM INFORMATION
echo "===== SYSTEM INFORMATION =====\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Operating System: " . PHP_OS . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "Server API: " . php_sapi_name() . "\n";
echo "Max Execution Time: " . ini_get('max_execution_time') . " seconds\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Max Upload Size: " . ini_get('upload_max_filesize') . "\n";
echo "Post Max Size: " . ini_get('post_max_size') . "\n";
echo "Max Input Vars: " . ini_get('max_input_vars') . "\n";
echo "Display Errors: " . (ini_get('display_errors') ? 'On' : 'Off') . "\n";
echo "Error Reporting Level: " . ini_get('error_reporting') . "\n";
echo "Default Charset: " . ini_get('default_charset') . "\n";
echo "Output Buffering: " . (ini_get('output_buffering') ? 'On' : 'Off') . "\n";
echo "Disabled Functions: " . (ini_get('disable_functions') ?: 'None') . "\n\n";

// EXTENSIONS CHECK
echo "===== CRITICAL EXTENSIONS CHECK =====\n";
$requiredExtensions = [
    'mysqli', 'pdo', 'pdo_mysql', 'json', 'session', 
    'mbstring', 'gd', 'xml', 'curl', 'openssl'
];

foreach ($requiredExtensions as $ext) {
    echo $ext . ": " . (extension_loaded($ext) ? "Available" : "NOT AVAILABLE") . "\n";
}
echo "\n";

// DATABASE CONNECTIVITY TEST
echo "===== DATABASE CONNECTIVITY =====\n";

// Function to test database connection with various methods
function testDatabaseConnection() {
    $results = [];
    $host = 'localhost'; // Most common default
    
    // Test MySQLi procedural
    $results['mysqli_procedural'] = 'Failed - Could not test (provide credentials)';
    
    // Test MySQLi OO 
    $results['mysqli_oo'] = 'Failed - Could not test (provide credentials)';
    
    // Test PDO
    $results['pdo'] = 'Failed - Could not test (provide credentials)';
    
    // Get MySQL/MariaDB version info without requiring connection
    if (function_exists('mysqli_get_client_info')) {
        $results['mysqli_client'] = mysqli_get_client_info();
    }
    
    if (class_exists('PDO')) {
        $drivers = PDO::getAvailableDrivers();
        $results['pdo_drivers'] = implode(', ', $drivers);
    }
    
    return $results;
}

$dbResults = testDatabaseConnection();
foreach ($dbResults as $key => $value) {
    echo "$key: $value\n";
}
echo "\n";

// FILE SYSTEM TESTS
echo "===== FILE SYSTEM TESTS =====\n";

// Test directory permissions and path information
$directoriesToTest = [
    'Current Directory' => '.',
    'Document Root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    'Upload Directory' => ini_get('upload_tmp_dir') ?: sys_get_temp_dir(),
    'Session Save Path' => ini_get('session.save_path') ?: 'Default'
];

foreach ($directoriesToTest as $name => $path) {
    $realPath = realpath($path);
    $isWritable = is_writable($path) ? 'Yes' : 'No';
    echo "$name: $path\n";
    echo "  Real Path: " . ($realPath ?: 'Unknown') . "\n";
    echo "  Writable: $isWritable\n";
    
    if ($realPath) {
        $perms = substr(sprintf('%o', fileperms($realPath)), -4);
        echo "  Permissions: $perms\n";
        
        // Try to get owner/group info if supported
        if (function_exists('posix_getpwuid') && function_exists('posix_getgrgid')) {
            $owner = posix_getpwuid(fileowner($realPath));
            $group = posix_getgrgid(filegroup($realPath));
            echo "  Owner: " . ($owner['name'] ?? 'Unknown') . "\n";
            echo "  Group: " . ($group['name'] ?? 'Unknown') . "\n";
        }
    }
    echo "\n";
}

// SESSION HANDLING
echo "===== SESSION CONFIGURATION =====\n";
echo "Session Save Handler: " . ini_get('session.save_handler') . "\n";
echo "Session Use Cookies: " . (ini_get('session.use_cookies') ? 'Yes' : 'No') . "\n";
echo "Cookie Secure: " . (ini_get('session.cookie_secure') ? 'Yes' : 'No') . "\n";
echo "Cookie HttpOnly: " . (ini_get('session.cookie_httponly') ? 'Yes' : 'No') . "\n";
echo "Session Strict Mode: " . (ini_get('session.use_strict_mode') ? 'Yes' : 'No') . "\n\n";

// DATE/TIME CONFIGURATION
echo "===== DATE/TIME CONFIGURATION =====\n";
echo "Default Timezone: " . date_default_timezone_get() . "\n";
echo "Current Server Time: " . date('Y-m-d H:i:s') . "\n";
echo "Timezone from PHP.ini: " . ini_get('date.timezone') . "\n\n";

// ENVIRONMENT VARIABLES
echo "===== ENVIRONMENT VARIABLES =====\n";
$importantVars = [
    'DOCUMENT_ROOT', 'SERVER_NAME', 'SERVER_ADDR', 
    'SERVER_PORT', 'REMOTE_ADDR', 'HTTPS'
];

foreach ($importantVars as $var) {
    echo "$var: " . ($_SERVER[$var] ?? 'Not set') . "\n";
}
echo "\n";

// SSL/TLS CONFIGURATION
echo "===== SSL/TLS CONFIGURATION =====\n";
echo "HTTPS: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'Enabled' : 'Disabled') . "\n";
if (function_exists('openssl_get_cert_locations')) {
    echo "OpenSSL Cert Locations:\n";
    $certLocations = openssl_get_cert_locations();
    foreach ($certLocations as $key => $value) {
        echo "  $key: $value\n";
    }
}
echo "\n";

// Include additional server-specific information
echo "===== SERVER IDENTIFICATION =====\n";
echo "Server Hostname: " . (gethostname() ?: 'Unknown') . "\n";
echo "Server IP: " . ($_SERVER['SERVER_ADDR'] ?? 'Unknown') . "\n";
echo "Script Path: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'Unknown') . "\n\n";

// CUSTOM SERVER TESTS
echo "===== ADDITIONAL SERVER TESTS =====\n";

// Check for mod_rewrite if on Apache
if (isset($_SERVER['SERVER_SOFTWARE']) && stripos($_SERVER['SERVER_SOFTWARE'], 'apache') !== false) {
    echo "Apache detected, checking for mod_rewrite...\n";
    
    // This is a basic check, not 100% reliable
    if (function_exists('apache_get_modules')) {
        $modules = apache_get_modules();
        echo "mod_rewrite: " . (in_array('mod_rewrite', $modules) ? 'Enabled' : 'Not found') . "\n";
    } else {
        echo "mod_rewrite: Unable to detect (CGI/FastCGI environment)\n";
    }
}

// Output buffering settings
echo "Output Buffering Level: " . ob_get_level() . "\n";
echo "Output Buffering Size: " . ini_get('output_buffering') . "\n\n";

// URL CONNECTION TEST
echo "===== URL CONNECTION TEST =====\n";
echo "Testing external connectivity...\n";

// Simple function to test connectivity
function testUrlConnection($url) {
    $result = "Unknown";
    
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            $result = "Failed: $error";
        } else {
            $result = "Success (HTTP $httpCode)";
        }
    } else if (ini_get('allow_url_fopen')) {
        $context = stream_context_create([
            'http' => ['timeout' => 5],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
        ]);
        
        $result = @file_get_contents($url, false, $context) !== false ? 
            "Success (using file_get_contents)" : 
            "Failed (using file_get_contents)";
    } else {
        $result = "Failed: No available method to test URL connection";
    }
    
    return $result;
}

// Test HTTPS and HTTP connections
echo "HTTPS Connection: " . testUrlConnection("https://www.google.com") . "\n";
echo "HTTP Connection: " . testUrlConnection("http://www.google.com") . "\n\n";

// Get the complete diagnostics output
$output = ob_get_clean();
echo $output;

// Also create a downloadable file with results
$filename = 'cms_server_diagnostic_' . date('Ymd_His') . '.txt';
file_put_contents($filename, $output);
echo "\nDiagnostic information has been saved to $filename\n";
echo "Please download this file and include it with your server information package.\n";
?>