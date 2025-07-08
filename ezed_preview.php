<?php
// Minimal authentication to prevent direct access
require_once('ezed_auth.php');

class PreviewManager {
    private $db;
    private $page_id;
    private $content_id;
    private $content;
    private $page_info;

    public function __construct($db) {
        $this->db = $db;
        $this->initializePage();
    }

    private function initializePage() {
        // Check for content_id first (from backup view)
        $this->content_id = filter_input(INPUT_GET, 'content_id', FILTER_VALIDATE_INT);
        
        if ($this->content_id) {
            // Lookup page_id from content_id
            $stmt = $this->db->prepare("SELECT page_id, contents FROM content WHERE content_id = ?");
            $stmt->bind_param("i", $this->content_id);
            $stmt->execute();
            $stmt->bind_result($page_id, $content);
            
            if ($stmt->fetch()) {
                $this->page_id = $page_id;
                $this->content = stripslashes($content);  // Store content directly
            } else {
                throw new Exception('Content record not found');
            }
            $stmt->close();
        } else {
            // Fallback to direct page_id (from content editor)
            $this->page_id = filter_input(INPUT_GET, 'page_id', FILTER_VALIDATE_INT);
            if (!$this->page_id) {
                throw new Exception('Missing parameter: Either content_id or page_id must be provided');
            }
        }

        // Retrieve page information
        $stmt = $this->db->prepare("SELECT * FROM pages WHERE page_id = ?");
        $stmt->bind_param("i", $this->page_id);
        $stmt->execute();
        
        // Use bind_result for compatibility
        $stmt->bind_result(
            $page_id, $name, $file, $title, $keywords, 
            $description, $extra_info, $header_image, 
            $header_file, $footer_file, $editable
        );
        
        if (!$stmt->fetch()) {
            throw new Exception('Page not found');
        }
        
        // Sanitize and validate header/footer files
        $safe_header_file = (!empty($header_file) && $header_file !== 'Y') ? $header_file : 'header.php';
        $safe_footer_file = (!empty($footer_file) && $footer_file !== 'Y') ? $footer_file : 'footer.php';
        
        $this->page_info = [
            'page_id' => $page_id,
            'name' => $name,
            'file' => $file,
            'title' => $title,
            'keywords' => $keywords,
            'description' => $description,
            'extra_info' => $extra_info,
            'header_image' => $header_image,
            'header_file' => $safe_header_file,
            'footer_file' => $safe_footer_file,
            'editable' => $editable
        ];
        
        $stmt->close();
    }

    private function retrieveContent() {
        // If we already have content from content_id, use that
        if (isset($this->content) && !empty($this->content)) {
            return;
        }
        
        // FIXED: Check first for POSTed content from CKEditor
        if (isset($_POST['content']) && !empty($_POST['content'])) {
            $this->content = $_POST['content'];
            return;
        }
        
        // Fall back to temporary content if no POST data
        $stmt = $this->db->prepare("SELECT contents FROM content WHERE page_id = ? AND status = 'T'");
        $stmt->bind_param("i", $this->page_id);
        $stmt->execute();
        $stmt->bind_result($temp_content);
        
        if ($stmt->fetch()) {
            $this->content = stripslashes($temp_content);
            $stmt->close();
            return;
        }
        $stmt->close();
        
        // If no temp content, get active content
        $stmt = $this->db->prepare("SELECT contents FROM content WHERE page_id = ? AND status = 'A'");
        $stmt->bind_param("i", $this->page_id);
        $stmt->execute();
        $stmt->bind_result($active_content);
        
        if ($stmt->fetch()) {
            $this->content = stripslashes($active_content);
        } else {
            $this->content = 'No content available';
        }
        $stmt->close();
    }

    public function generatePreview() {
        // Retrieve content if not already set
        $this->retrieveContent();
        
        // Set session variables for header/footer
        $_SESSION['page_title'] = $this->page_info['title'];
        $_SESSION['page_keywords'] = $this->page_info['keywords'];
        $_SESSION['page_description'] = $this->page_info['description'];
        $_SESSION['extra_info'] = $this->page_info['extra_info'];
        $_SESSION['header_image'] = $this->page_info['header_image'];
        
        // Start output buffering
        ob_start();
        
        // Include header
        try {
            if ($this->page_id == 1) {
                include("header_index.php");
            } else {
                $header_path = $this->page_info['header_file'];
                
                if (!file_exists($header_path)) {
                    echo "<!-- Header file not found: " . htmlspecialchars($this->page_info['header_file']) . " -->";
                } else {
                    include($header_path);
                }
            }
        } catch (Exception $e) {
            echo "<!-- Header include error: " . htmlspecialchars($e->getMessage()) . " -->";
        }
        
        // Output content
        echo $this->content;
        
        // Include footer
        try {
            if ($this->page_id == 1) {
                include("footer_index.php");
            } else {
                $footer_path = $this->page_info['footer_file'];
                
                if (!file_exists($footer_path)) {
                    echo "<!-- Footer file not found: " . htmlspecialchars($this->page_info['footer_file']) . " -->";
                } else {
                    include($footer_path);
                }
            }
        } catch (Exception $e) {
            echo "<!-- Footer include error: " . htmlspecialchars($e->getMessage()) . " -->";
        }
        
        // Get the complete HTML
        $preview_html = ob_get_clean();
        
        // Write to preview file
        $preview_filename = $this->page_id == 1 ? "preview.html" : "preview.htm";
        $preview_path = $_SERVER['DOCUMENT_ROOT'] . "/" . $preview_filename;
        
        file_put_contents($preview_path, $preview_html);
        
        // Redirect to preview
        header("Location: /" . $preview_filename);
        exit;
    }
}

// Main execution
try {
    $previewManager = new PreviewManager($db);
    $previewManager->generatePreview();
} catch (Exception $e) {
    // Log the error for admin review
    error_log("Preview Generation Error: " . $e->getMessage());
    
    // Display a generic user-friendly error
    echo "<html><body>";
    echo "<h1>Preview Unavailable</h1>";
    echo "<p>We're sorry, but the preview could not be generated at this time.</p>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</body></html>";
    exit;
}
?>