<?php
// fetch_help.php
function get_help_content() {
    $url = 'https://www.dancewebsitehosting.com/s2e_support/mainhelp.php';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // Sometimes needed for HTTPS
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);      // 5 second connection timeout
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);            // 10 second timeout
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; S2EAdmin/1.0)');
    
    $content = curl_exec($ch);
    $error = curl_error($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    
    if ($content === false) {
        error_log("cURL Error: " . $error);
        error_log("cURL Info: " . print_r($info, true));
        return 'Unable to load help content. Please try again later.';
    }
    
    return $content;
}

// Cache the help content for 1 hour
$cache_file = sys_get_temp_dir() . '/help_content.cache';
if (!file_exists($cache_file) || (time() - filemtime($cache_file) > 3600)) {
    $content = get_help_content();
    file_put_contents($cache_file, $content);
} else {
    $content = file_get_contents($cache_file);
}

echo $content;
?>