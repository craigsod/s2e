<?php
// Simple proxy script to fetch external content

// Set caching headers
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Content-Type: text/html; charset=utf-8');

// Determine which help file to fetch based on the 'type' parameter
$help_type = isset($_GET['type']) ? $_GET['type'] : 'main';

// Select the appropriate URL
if ($help_type == 'content') {
    $remote_url = 'https://www.studioofdance.com/s2e_support/content_page_help.php';
} else {
    $remote_url = 'https://www.studioofdance.com/s2e_support/mainhelp.php';
}

// Add timestamp to prevent caching
$remote_url .= '?t=' . time();

// Initialize curl session
$ch = curl_init();

// Set curl options
curl_setopt($ch, CURLOPT_URL, $remote_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; ProxyFetch/1.0)');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Only if needed for SSL issues
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Only if needed for SSL issues

// Execute curl request
$response = curl_exec($ch);
$error = curl_error($ch);
$info = curl_getinfo($ch);
curl_close($ch);

// Check for errors
if ($error) {
    echo "<!-- Error fetching content: " . htmlspecialchars($error) . " -->";
    echo "<p>Error loading help content. Please try again later.</p>";
    exit;
}

// Check response code
if ($info['http_code'] != 200) {
    echo "<!-- HTTP Error: " . $info['http_code'] . " -->";
    echo "<p>Error loading help content (HTTP " . $info['http_code'] . "). Please try again later.</p>";
    exit;
}

// Output the content
echo $response;
?>