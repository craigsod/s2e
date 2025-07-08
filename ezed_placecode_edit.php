<?php
require_once('ezed_auth.php');

try {
    $placecode_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$placecode_id) {
        throw new Exception('Invalid placecode ID');
    }

    // Get current placecode using bind_result instead of get_result
    $stmt = $db->prepare("SELECT placecode_id, placecode, status, placecode_description, placecode_code FROM placecodes WHERE placecode_id = ?");
    $stmt->bind_param("i", $placecode_id);
    $stmt->execute();
    
    // Bind variables to store the result
    $stmt->bind_result($db_placecode_id, $db_placecode, $db_status, $db_placecode_description, $db_placecode_code);
    
    // Fetch the data
    $found = $stmt->fetch();
    
    if (!$found) {
        throw new Exception('Placecode not found');
    }
    
    // Store values in an array to match the previous format
    $placecode = [
        'placecode_id' => $db_placecode_id,
        'placecode' => $db_placecode,
        'status' => $db_status,
        'placecode_description' => $db_placecode_description,
        'placecode_code' => $db_placecode_code
    ];
    
    $stmt->close();

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
        // Update placecode
        $update_stmt = $db->prepare("UPDATE placecodes SET status = ?, placecode_description = ?, placecode_code = ? WHERE placecode_id = ?");
        $update_stmt->bind_param("sssi", 
            $_POST['placecode_status'],
            $_POST['placecode_desc'],
            htmlentities($_POST['placecode_code'], ENT_QUOTES, "UTF-8"),
            $placecode_id
        );
        $update_stmt->execute();
        $update_stmt->close();

        // Get affected pages using bind_result
        $pages_stmt = $db->prepare("SELECT page_id FROM placecode_page WHERE placecode = ?");
        $pages_stmt->bind_param("s", $placecode['placecode']);
        $pages_stmt->execute();
        $pages_stmt->bind_result($page_id);
        
        // Loop through affected pages
        while ($pages_stmt->fetch()) {
            createHTMLfile($page_id, $db, $database_studioAdmin);
        }
        
        $pages_stmt->close();

        header("Location: ezed_placecode_management.php");
        exit;
    }

} catch (Exception $e) {
    handleError('Placecode Edit Error', $e->getMessage());
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
    <script>
    function pageChange(select) {
        const pageId = select.value;
        if (!pageId) return false;
        window.location.href = 'ezed_content.php?page_id=' + pageId;
        return false;
    }
    </script>
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
                                                <p align="left" class="subhead">Edit Placecode</p>
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
                                                <form method="post" action="">
                                                    <p align="left">
                                                        <span class="text"><strong>Description:</strong></span><br>
                                                        <input name="placecode_desc" type="text" value="<?= htmlspecialchars(stripslashes($placecode['placecode_description'])) ?>" size="30" required>
                                                    </p>

                                                    <p align="left">
                                                        <span class="text"><strong>Placecode status:</strong></span><br>
                                                        <input name="placecode_status" type="radio" value="Active" <?= $placecode['status'] === 'Active' ? 'checked' : '' ?>> 
                                                        <span class="text">Active</span>
                                                        <input name="placecode_status" type="radio" value="Inactive" <?= $placecode['status'] === 'Inactive' ? 'checked' : '' ?>>
                                                        <span class="text">Inactive</span>
                                                    </p>

                                                    <p align="left">
                                                        <span class="text"><strong>Code:</strong></span><br>
                                                        <textarea name="placecode_code" cols="100" rows="5" required><?= htmlspecialchars_decode($placecode['placecode_code']) ?></textarea>
                                                    </p>

                                                    <p align="left">
                                                        <input type="submit" name="submit" value="Save changes">
                                                    </p>

                                                    <p align="left" class="text">
                                                        <a href="ezed_placecode_management.php">Return to placecode management page</a>
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
</body>
</html>