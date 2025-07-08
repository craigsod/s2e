<?php
function createHTMLfile($page_id, $db) {
    try {
        // Get page information
        $stmt = $db->prepare("SELECT page_id, name, file, title, keywords, description, extra_info, header_image, header_file, footer_file, editable FROM pages WHERE page_id = ?");
        $stmt->bind_param("i", $page_id);
        $stmt->execute();
        
        // Using bind_result instead of get_result
        $stmt->bind_result(
            $page_info_page_id, 
            $page_info_name, 
            $page_info_file, 
            $page_info_title, 
            $page_info_keywords, 
            $page_info_description, 
            $page_info_extra_info,
            $page_info_header_image,
            $page_info_header_file,
            $page_info_footer_file,
            $page_info_editable
        );
        
        $has_page = $stmt->fetch();
        $stmt->close();
        
        if (!$has_page) {
            throw new Exception('Page not found');
        }
        
        // Create page array to use below
        $page = array(
            'page_id' => $page_info_page_id,
            'name' => $page_info_name,
            'file' => $page_info_file,
            'title' => $page_info_title,
            'keywords' => $page_info_keywords,
            'description' => $page_info_description,
            'extra_info' => $page_info_extra_info,
            'header_image' => $page_info_header_image,
            'header_file' => $page_info_header_file,
            'footer_file' => $page_info_footer_file,
            'editable' => $page_info_editable
        );
        
        // Determine header and footer files
        $header_file = $page['header_file'] ?: "header.php";
        $footer_file = $page['footer_file'] ?: "footer.php";
        
        // Get page content
        $content_stmt = $db->prepare("SELECT content_id, page_id, contents, created, updated, status FROM content WHERE page_id = ? AND status = 'A'");
        $content_stmt->bind_param("i", $page_id);
        $content_stmt->execute();
        
        // Using bind_result instead of get_result
        $content_stmt->bind_result(
            $content_id, 
            $content_page_id, 
            $content_contents, 
            $content_created, 
            $content_updated, 
            $content_status
        );
        
        $has_content = $content_stmt->fetch();
        $content_stmt->close();
        
        if (!$has_content) {
            throw new Exception('No active content found for page');
        }
        
        $content = $content_contents;
        
        // Set session variables needed by header/footer
        $_SESSION['page_title'] = $page['title'];
        $_SESSION['page_keywords'] = $page['keywords'];
        $_SESSION['page_description'] = $page['description'];
        $_SESSION['extra_info'] = $page['extra_info'];
        $_SESSION['header_image'] = $page['header_image'];
        
        // Build the page
        ob_start();
        
        if ($page_id == 1) {
            include("header_index.php");
        } else {
            include($header_file);
        }
        
        echo stripslashes($content);
        
        if ($page_id == 1) {
            include("footer_index.php");
        } else {
            include($footer_file);
        }
        
        $page_content = ob_get_clean();
        
        // Write the file
        $filename = "../" . $page['file'];
        $extension = ($page_id == 1) ? '.html' : '.htm';
        $file = fopen($filename . $extension, 'w');
        
        if (!$file) {
            throw new Exception('Unable to create HTML file');
        }
        
        fwrite($file, $page_content);
        fclose($file);
        
        return true;
        
    } catch (Exception $e) {
        error_log("createHTMLfile error: " . $e->getMessage());
        return false;
    }
}

// Empty placeholder function that does nothing
function processPlacecodes($page_id, $content, $db) {
    // Simply return the content unmodified
    return $content;
}
?>