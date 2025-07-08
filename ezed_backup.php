<?php
declare(strict_types=1);

require_once('ezed_auth.php');

class BackupManager {
    private mysqli $db;
    private int $page_id;
    private array $backups = [];
    private int $total_rows = 0;
    private int $current_page = 0;
    private int $max_rows = 20;
    private int $total_pages = 0;
    private string $page_name = '';

    public function __construct(mysqli $db) {
        $this->db = $db;
        $this->initializePageId();
        $this->loadBackups();
    }

    private function initializePageId(): void {
        $page_id = filter_input(INPUT_POST, 'page_id', FILTER_VALIDATE_INT) ?? 
                   filter_input(INPUT_GET, 'page_id', FILTER_VALIDATE_INT);

        if (!$page_id || $page_id < 1 || $page_id > 99) {
            throw new Exception('Invalid page ID provided');
        }

        $this->page_id = $page_id;
        
        // Get page name
        $stmt = $this->db->prepare("SELECT name FROM pages WHERE page_id = ?");
        $stmt->bind_param("i", $this->page_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $page = $result->fetch_assoc();
        $this->page_name = $page['name'] ?? '';
        $stmt->close();
    }

    private function loadBackups(): void {
        $this->current_page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?? 0;
        $start_row = $this->current_page * $this->max_rows;

        // Get total count first
        $count_query = "SELECT COUNT(*) as total FROM content 
                       WHERE page_id = ? AND status = 'B'";
        $stmt = $this->db->prepare($count_query);
        $stmt->bind_param("i", $this->page_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $this->total_rows = $result->fetch_assoc()['total'];
        $this->total_pages = (int)ceil($this->total_rows / $this->max_rows) - 1;

        // Get paginated backups
        $query = "SELECT *, DATE_FORMAT(updated, '%b %e, %Y %h:%i %p') AS formatted_date 
                 FROM content 
                 WHERE page_id = ? AND status = 'B' 
                 ORDER BY updated DESC 
                 LIMIT ?, ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iii", $this->page_id, $start_row, $this->max_rows);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $this->backups[] = $row;
        }
    }

    public function hasBackups(): bool {
        return !empty($this->backups);
    }

    public function getBackups(): array {
        return $this->backups;
    }

    public function getPageId(): int {
        return $this->page_id;
    }

    public function getPageName(): string {
        return $this->page_name;
    }
}

try {
    $backupManager = new BackupManager($db);
} catch (Exception $e) {
    handleError('Backup Manager Error', $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Previously Saved Pages</title>
    <link href="css/edit1.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor="443B34">
    <div align="center">
        <table width="780" border="1" cellpadding="4" cellspacing="0" bordercolor="#999999" bgcolor="#FFFFFF">
            <tr>
                <td valign="top" bordercolor="#FFFFFF">
                    <div align="center">
                        <table width="100%" border="0" align="left" cellspacing="0">
                            <tr>
                                <td class="secondlink">
                                    <blockquote>
                                      <p align="left" class="subhead">
                                            <strong>Backups of the: <?= htmlspecialchars($backupManager->getPageName()) ?> page</strong>
                                        </p>
                                        <?php if ($backupManager->hasBackups()): ?>
                                    
                                    <ul class="radioLabel">
                                      <li>Click on the VIEW link to see the backed up version of the page</li>
                                      <li>
                                        <span class="radioLabel">Click on the RESTORE link to restore this page back to the date listed</span></li>
                                      <li class="radioLabel">
                                      The most recent back-up is at top of this list. All times are Central Time.                               </li>
                                    </ul>
                                    <p class="radioLabel">NOTE: Clicking the RESTORE link will update the page on the website with the contents of the backup.</p>
                                      <p>
                                        <a href="ezed_content.php?page_id=<?= htmlspecialchars((string)$backupManager->getPageId()) ?>" 
                                                   class="text">
                                          <strong>Return to page edit view</strong>
                                        </a>
                                      </p>
                                      <table width="500" border="1" align="left" cellpadding="6" cellspacing="0" bordercolor="#999999">
                                        <tr>
                                          <th width="264" scope="col">
                                            <span class="radioLabel">Date page was backed up</span>
                                          </th>
                                          <th width="126" scope="col">&nbsp;</th>
                                          <th width="126" scope="col">&nbsp;</th>
                                        </tr>
                                        <?php foreach ($backupManager->getBackups() as $backup): ?>
                                        <tr>
                                          <td>
                                            <div align="left" class="text style4">
                                              <?= htmlspecialchars($backup['formatted_date']) ?>
                                            </div>
                                          </td>
                                          <td>
                                            <div align="center" class="text">
                                              <a href="ezed_preview.php?content_id=<?= htmlspecialchars((string)$backup['content_id']) ?>" 
                                                                   target="_blank" 
                                                                   class="link">VIEW</a>
                                            </div>
                                          </td>
                                          <td>
                                            <div align="center">
                                              <a href="ezed_restore.php?content_id=<?= htmlspecialchars((string)$backup['content_id']) ?>&page_id=<?= htmlspecialchars((string)$backup['page_id']) ?>" 
                                                                   class="link">RESTORE</a>
                                            </div>
                                          </td>
                                        </tr>
                                        <?php endforeach; ?>
                                      </table>
                                      <?php else: ?>
                                      <p align="left" class="subhead">
                                        <strong>There are no backup copies for this page<br>
                                        <span class="text">Get busy and make some changes so your site looks fresh.</span></strong>
                                      </p>
                                      <p>
                                        <a href="ezed_content.php?page_id=<?= htmlspecialchars((string)$backupManager->getPageId()) ?>" 
                                                   class="text">
                                          <strong>Return to page edit view</strong>
                                        </a>
                                      </p>
                                      <?php endif; ?>
                                    </blockquote>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>