<?php
require_once('ezed_auth.php');

try {
    // Get all editable pages using bind_result instead of get_result
    $stmt = $db->prepare("SELECT page_id, name FROM pages WHERE editable = 'Y' ORDER BY name");
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $db->error);
    }
    
    $stmt->execute();
    
    // Bind variables to the prepared statement
    $stmt->bind_result($page_id, $page_name);
    
    // Create an array to store all pages
    $all_pages = [];
    while ($stmt->fetch()) {
        $all_pages[] = [
            'page_id' => $page_id,
            'name' => $page_name
        ];
    }
    
    $num_pages = count($all_pages);
    
    if ($num_pages === 0) {
        throw new Exception('No editable pages found');
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    handleError('Auto Build Form Error', $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($_SESSION['site_name']) ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="css/edit1.css" rel="stylesheet" type="text/css">
    <?php include_once("ezed_modal_code.php"); ?>
</head>
<body bgcolor="443B34">
    <div align="center">
        <table width="1170" border="1" cellpadding="0" cellspacing="0" bordercolor="#000000" bgcolor="#FFFFFF">
            <tr>
                <td valign="top" bordercolor="#FFFFFF">
                    <div align="center">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" bordercolor="#999999">
                            <tr bgcolor="#FFFFFF">
                                <td colspan="3">
                                    <table border="0" cellpadding="8" cellspacing="0" width="1000">
                                        <tr>
                                            <td height="50px">
                                                <p align="left" class="subhead">Rebuild Pages</p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <?php include 'ezed_sidebar.php'; ?>
                                <td width="0%" valign="top" bgcolor="#DFF1FF">&nbsp;</td>
                                <td width="88%" valign="top">
                                    <div style="background-color:#DFF1FF; height:5px;"></div>
                                    <table width="100%" border="1" cellpadding="8" cellspacing="0">
                                        <tr>
                                            <td valign="top">
                                                <p class="text">Uncheck the pages you do NOT want to rebuild:</p>
                                                
                                                <form action="ezed_auto_build.php" method="post">
                                                    <div class="text" style="margin: 20px 0;">
                                                        <?php foreach($all_pages as $page): ?>
                                                            <div style="margin: 10px 0;">
                                                                <input 
                                                                    type="checkbox" 
                                                                    name="page<?= $page['page_id'] ?>" 
                                                                    id="page<?= $page['page_id'] ?>" 
                                                                    checked="checked"
                                                                >
                                                                <label for="page<?= $page['page_id'] ?>">
                                                                    <?= htmlspecialchars($page['name']) ?>
                                                                </label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    
                                                    <div style="margin: 20px 0;">
                                                        <input type="submit" name="submit" value="Build selected pages" class="button">
                                                    </div>
                                                    
                                                    <p>
                                                        <a href="ezed_site_admin.php" class="text">Return to site administration</a>
                                                    </p>
                                                </form>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <script>
    function pageChange(select) {
        const pageId = select.value;
        if (!pageId) return false;
        window.location.href = 'ezed_content.php?page_id=' + pageId;
        return false;
    }
    </script>
</body>
</html>