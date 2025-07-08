<?php
// Force error display
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Create a simple log function
function simple_log($message) {
    file_put_contents('content_update.log', date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

// Log script start
simple_log("Script started");

// Include required files
require_once('ezed_auth.php');
require_once('ezed_create_HTML_function.php');

simple_log("Files included");

// Get parameters
$page_id = isset($_GET['page_id']) ? (int)$_GET['page_id'] : 0;
$status = isset($_GET['status']) ? $_GET['status'] : null;
$is_temp = ($status === 'T');

simple_log("Page ID: $page_id, Status: " . ($status ?? 'null'));

// Validate page_id
if ($page_id <= 0) {
    simple_log("Invalid page ID");
    die("Invalid page ID");
}

// Get content
$content = '';
if (isset($_POST['content'])) {
    $content = $_POST['content'];
    simple_log("Content received: " . strlen($content) . " bytes");
} else {
    simple_log("No content in POST");
}

// Main process
try {
    simple_log("Starting content update process");
    
    if ($is_temp) {
        // Temporary save
        simple_log("Performing temporary save");
        
        // Check if temporary content already exists
        $check_stmt = $db->prepare("SELECT content_id FROM content WHERE page_id = ? AND status = 'T'");
        $check_stmt->bind_param("i", $page_id);
        $check_stmt->execute();
        $check_stmt->store_result();
        $has_temp = $check_stmt->num_rows > 0;
        $check_stmt->close();
        
        if ($has_temp) {
            // Update existing
            simple_log("Updating existing temporary content");
            $update_stmt = $db->prepare("UPDATE content SET contents = ?, updated = NOW() WHERE page_id = ? AND status = 'T'");
            $update_stmt->bind_param("si", $content, $page_id);
            $update_stmt->execute();
            $update_stmt->close();
        } else {
            // Insert new
            simple_log("Creating new temporary content");
            $insert_stmt = $db->prepare("INSERT INTO content (page_id, status, created, updated, contents) VALUES (?, 'T', NOW(), NOW(), ?)");
            $insert_stmt->bind_param("is", $page_id, $content);
            $insert_stmt->execute();
            $insert_stmt->close();
        }
    } else {
        // Permanent save
        simple_log("Performing permanent save");
        
        // 1. Check if active content exists
        $check_stmt = $db->prepare("SELECT content_id FROM content WHERE page_id = ? AND status = 'A'");
        $check_stmt->bind_param("i", $page_id);
        $check_stmt->execute();
        $check_stmt->store_result();
        $has_active = $check_stmt->num_rows > 0;
        $check_stmt->close();
        
        // 2. Backup existing active content (if it exists)
        if ($has_active) {
            simple_log("Backing up active content");
            $backup_stmt = $db->prepare("INSERT INTO content (page_id, status, created, updated, contents) SELECT page_id, 'B', NOW(), NOW(), contents FROM content WHERE page_id = ? AND status = 'A'");
            $backup_stmt->bind_param("i", $page_id);
            $backup_stmt->execute();
            $backup_stmt->close();
            
            // 3. Update active content
            simple_log("Updating active content");
            $update_stmt = $db->prepare("UPDATE content SET contents = ?, updated = NOW() WHERE page_id = ? AND status = 'A'");
            $update_stmt->bind_param("si", $content, $page_id);
            $update_stmt->execute();
            $update_stmt->close();
        } else {
            // 3. Create new active content
            simple_log("Creating new active content");
            $insert_stmt = $db->prepare("INSERT INTO content (page_id, status, created, updated, contents) VALUES (?, 'A', NOW(), NOW(), ?)");
            $insert_stmt->bind_param("is", $page_id, $content);
            $insert_stmt->execute();
            $insert_stmt->close();
        }
        
        // 4. Delete temporary content
        simple_log("Deleting temporary content");
        $delete_stmt = $db->prepare("DELETE FROM content WHERE page_id = ? AND status = 'T'");
        $delete_stmt->bind_param("i", $page_id);
        $delete_stmt->execute();
        $delete_stmt->close();
        
        // 5. Create HTML file
        simple_log("Creating HTML file");
        $html_result = createHTMLfile($page_id, $db);
        simple_log("HTML file creation result: " . ($html_result ? "Success" : "Failed"));
    }
    
    // Set session flag and redirect
    $_SESSION['updatesave'] = true;
    simple_log("Setting redirect to content page");
    header("Location: ezed_content.php?page_id=$page_id");
    exit;
    
} catch (Exception $e) {
    simple_log("Error: " . $e->getMessage());
    echo "Error: " . $e->getMessage();
    exit;
}

simple_log("Script end (unexpected)");
?>