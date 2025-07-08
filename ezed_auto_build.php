<?php
require_once('ezed_auth.php');
require_once('ezed_create_HTML_function.php');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Process each checked page
    foreach($_POST as $key => $value) {
        if (strpos($key, 'page') === 0) {
            $page_id = (int)substr($key, 4);
            if ($page_id > 0) {
                if (!createHTMLfile($page_id, $db)) {
                    error_log("Failed to rebuild page ID: " . $page_id);
                }
            }
        }
    }
    
    // Redirect back to site admin
    header("Location: ezed_site_admin.php");
    exit;
    
} catch (Exception $e) {
    handleError('Auto Build Error', $e->getMessage());
}
?>