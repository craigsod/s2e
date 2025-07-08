<?php
declare(strict_types=1);

require_once('ezed_auth.php');
require_once('ezed_create_HTML_function.php');

class ContentRestorer {
    private mysqli $db;
    private int $page_id;
    private int $content_id;

    public function __construct(mysqli $db) {
        $this->db = $db;
        $this->validateInputs();
    }

    private function validateInputs(): void {
        $this->page_id = filter_input(INPUT_GET, 'page_id', FILTER_VALIDATE_INT);
        $this->content_id = filter_input(INPUT_GET, 'content_id', FILTER_VALIDATE_INT);

        if (!$this->page_id || !$this->content_id) {
            throw new Exception('Invalid page_id or content_id provided');
        }

        // Verify the content exists and belongs to the specified page
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count FROM content WHERE content_id = ? AND page_id = ?"
        );
        $stmt->bind_param("ii", $this->content_id, $this->page_id);
        $stmt->execute();
        
        // Use bind_result instead of get_result
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count !== 1) {
            throw new Exception('Invalid content or page combination');
        }
    }

    public function restore(): void {
        try {
            $this->db->begin_transaction();

            // Step 1: Set current active content to backup
            $stmt = $this->db->prepare(
                "UPDATE content SET status = 'B' WHERE status = 'A' AND page_id = ?"
            );
            $stmt->bind_param("i", $this->page_id);
            if (!$stmt->execute()) {
                throw new Exception('Failed to backup current active content');
            }
            $stmt->close();

            // Step 2: Set selected backup content to active
            $stmt = $this->db->prepare(
                "UPDATE content SET status = 'A' WHERE content_id = ?"
            );
            $stmt->bind_param("i", $this->content_id);
            if (!$stmt->execute()) {
                throw new Exception('Failed to restore backup content');
            }
            $stmt->close();

            // Step 3: Generate new HTML file
            if (!createHTMLfile($this->page_id, $this->db)) {
                throw new Exception('Failed to create HTML file');
            }

            $this->db->commit();

        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception('Content restoration failed: ' . $e->getMessage());
        }
    }

    public function getReturnUrl(): string {
        return $_SESSION['ReturnTo'] ?? "ezed_content.php?page_id={$this->page_id}";
    }
}

try {
    $restorer = new ContentRestorer($db);
    $restorer->restore();
    
    // Redirect back to the appropriate page
    header("Location: " . $restorer->getReturnUrl());
    exit;

} catch (Exception $e) {
    handleError('Content Restore Error', $e->getMessage());
}
?>