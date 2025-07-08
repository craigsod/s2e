<?php
require_once('ezed_auth.php');
require_once('ezed_create_HTML_function.php');

try {
    $placecode_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$placecode_id) {
        throw new Exception('Invalid placecode ID');
    }

    // Get placecode information before deletion
    $stmt = $db->prepare("SELECT * FROM placecodes WHERE placecode_id = ?");
    $stmt->bind_param("i", $placecode_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $placecode = $result->fetch_assoc();

    if (!$placecode) {
        throw new Exception('Placecode not found');
    }

    // Get pages using this placecode
    $stmt = $db->prepare("SELECT p.*, c.content_id, c.contents 
                         FROM pages p 
                         INNER JOIN placecode_page pp ON p.page_id = pp.page_id 
                         INNER JOIN content c ON p.page_id = c.page_id 
                         WHERE pp.placecode = ? AND c.status = 'A'");
    $stmt->bind_param("s", $placecode['placecode']);
    $stmt->execute();
    $affected_pages = $stmt->get_result();
    $num_affected = $affected_pages->num_rows;

    // Update content and rebuild pages
    while ($page = $affected_pages->fetch_assoc()) {
        // Replace placecode with empty space in content
        $updated_content = str_replace($placecode['placecode'], '', $page['contents']);
        
        // Update the content in database
        $update_stmt = $db->prepare("UPDATE content SET contents = ? WHERE content_id = ?");
        $update_stmt->bind_param("si", $updated_content, $page['content_id']);
        $update_stmt->execute();

        // Rebuild the page
        createHTMLfile($page['page_id'], $db, $database_studioAdmin);
    }

    // Delete placecode_page entries
    $stmt = $db->prepare("DELETE FROM placecode_page WHERE placecode = ?");
    $stmt->bind_param("s", $placecode['placecode']);
    $stmt->execute();

    // Finally, delete the placecode
    $stmt = $db->prepare("DELETE FROM placecodes WHERE placecode_id = ?");
    $stmt->bind_param("i", $placecode_id);
    $stmt->execute();

} catch (Exception $e) {
    handleError('Placecode Delete Error', $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Delete Placecode</title>
    <meta charset="utf-8">
    <link href="css/edit1.css" rel="stylesheet" type="text/css">
</head>
<body>
    <div align="center">
        <table style="font-family:Verdana, Arial, Helvetica, sans-serif; font-size:12px;" 
               width="<?= $num_affected > 0 ? '650' : '400' ?>px" 
               height="<?= $num_affected > 0 ? '250' : '150' ?>px" 
               border="2" cellspacing="0" cellpadding="8" bordercolor="#999999">
            <tr>
                <td valign="top">
                    <blockquote>
                        <p>The placecode has been deleted.</p>
                        <?php if ($num_affected > 0): ?>
                            <p>The following pages have been updated:</p>
                            <?php 
                            $affected_pages->data_seek(0);
                            while ($page = $affected_pages->fetch_assoc()): 
                            ?>
                                &nbsp;&nbsp;&bull;&nbsp;<?= htmlspecialchars($page['name']) ?><br>
                            <?php endwhile; ?>
                            <p><strong>The pages have been automatically updated and rebuilt.</strong></p>
                        <?php endif; ?>
                    </blockquote>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
<?php
if (isset($result)) {
    $result->free();
}
if (isset($affected_pages)) {
    $affected_pages->free();
}
?>