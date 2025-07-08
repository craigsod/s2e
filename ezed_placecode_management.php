<?php
require_once('ezed_auth.php');

// Initialize variables outside the main scope
$all_placecodes = array(); // Using a different variable name to avoid any conflicts
$total_placecodes = 0;

try {
    // Get all placecodes directly using a regular query
    $query = "SELECT placecode_id, placecode_description, placecode, placecode_code, status FROM placecodes ORDER BY placecode_id";
    
    // Store result directly in a dedicated variable
    $result = $db->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $db->error);
    }
    
    // Create a copy of the results in a standalone array
    while ($row = $result->fetch_assoc()) {
        // Use copy instead of reference to avoid any overwrites
        $placecode_copy = array(
            'placecode_id' => $row['placecode_id'],
            'placecode_description' => $row['placecode_description'],
            'placecode' => $row['placecode'],
            'placecode_code' => $row['placecode_code'],
            'status' => $row['status']
        );
        
        // Add to the array
        $all_placecodes[] = $placecode_copy;
        $total_placecodes++;
    }
    
    $result->close();
    
    // Handle new placecode creation
    if (isset($_POST['placecode_desc']) && !empty($_POST['placecode_desc']) && !empty($_POST['placecode_code'])) {
        // Get the next placecode ID
        $stmt_max = $db->prepare("SELECT MAX(placecode_id) FROM placecodes");
        
        if (!$stmt_max) {
            throw new Exception("Prepare failed for MAX query: " . $db->error);
        }
        
        if (!$stmt_max->execute()) {
            throw new Exception("Execute failed for MAX query: " . $stmt_max->error);
        }
        
        $stmt_max->bind_result($max_id);
        $stmt_max->fetch();
        $stmt_max->close();
        
        $next_id = ($max_id) ? $max_id + 1 : 1;
        
        $newplacecode = "[[placecode" . $next_id . "]]";
        
        // Insert new placecode
        $stmt_insert = $db->prepare("INSERT INTO placecodes (placecode_id, placecode_description, placecode, placecode_code, status) VALUES (?, ?, ?, ?, ?)");
        
        if (!$stmt_insert) {
            throw new Exception("Prepare failed for INSERT: " . $db->error);
        }
        
        $placecode_code_sanitized = htmlentities($_POST['placecode_code'], ENT_QUOTES, "UTF-8");
        $stmt_insert->bind_param("issss", 
            $next_id,
            $_POST['placecode_desc'],
            $newplacecode,
            $placecode_code_sanitized,
            $_POST['placecode_status']
        );
        
        if (!$stmt_insert->execute()) {
            throw new Exception("Execute failed for INSERT: " . $stmt_insert->error);
        }
        
        $stmt_insert->close();
        
        header("Location: ezed_placecode_management.php");
        exit;
    }
} catch (Exception $e) {
    // Log the error and display a simple message
    error_log("Placecode Error: " . $e->getMessage());
    echo "An error occurred while processing placecodes. Please check the error log for details.";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= isset($_SESSION['site_name']) ? htmlspecialchars($_SESSION['site_name']) : 'Site Management' ?></title>
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
                                                <p align="left" class="subhead">Placecode Management</p>
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
                                            <td width="56%" valign="top">
                                                <p align="left" class="buttons"><strong>Existing Placecodes</strong></p>
                                                
                                                <!-- Debug section removed for production -->
                                                
                                                <?php 
                                                // Use direct conditional checks without relying on previously calculated values
                                                $display_table = (is_array($all_placecodes) && count($all_placecodes) > 0);
                                                ?>
                                                
                                                <?php if($display_table): ?>
                                                    <table width="98%" border="1" cellpadding="8" cellspacing="0" bordercolor="#CCCCCC">
                                                        <tr>
                                                            <td width="35%" class="text"><div align="left"><strong>Description</strong></div></td>
                                                            <td width="17%" class="text"><div align="left"><strong>Placecode*</strong></div></td>
                                                            <td width="13%" class="text"><div align="left"><strong>Status</strong></div></td>
                                                            <td width="15%" class="text" colspan="3"><div align="center"><strong>Actions</strong></div></td>
                                                        </tr>
                                                        <?php 
                                                        for($i = 0; $i < count($all_placecodes); $i++): 
                                                            // Create a local copy to avoid any reference issues
                                                            $current_placecode = $all_placecodes[$i];
                                                        ?>
                                                            <tr>
                                                                <td class="text"><div align="left"><?= htmlspecialchars(stripslashes($current_placecode['placecode_description'])) ?></div></td>
                                                                <td class="text"><div align="left"><?= htmlspecialchars($current_placecode['placecode']) ?></div></td>
                                                                <td class="text"><div align="left"><?= htmlspecialchars($current_placecode['status']) ?></div></td>
                                                                <td class="text"><div align="center">
                                                                    <a class="youtube" href="ezed_placecode_view.php?id=<?= $current_placecode['placecode_id'] ?>">View</a> |
                                                                    <a href="ezed_placecode_edit.php?id=<?= $current_placecode['placecode_id'] ?>">Edit</a> |
                                                                    <a class="callbacks" href="ezed_placecode_delete.php?id=<?= $current_placecode['placecode_id'] ?>">Delete</a>
                                                                </div></td>
                                                            </tr>
                                                        <?php endfor; ?>
                                                    </table>
                                                    <p class="text">* Copy and paste the placecode on any page to embed your code. Include the double brackets.</p>
                                                <?php else: ?>
                                                    <p class="text">No placecodes found. Use the form to create your first placecode.</p>
                                                <?php endif; ?>
                                            </td>
                                            <td width="44%" valign="top">
                                                <p align="left" class="buttons"><strong>Create New Placecode</strong></p>
                                                <form method="post" action="">
                                                    <p align="left">
                                                        <strong>Description:</strong><br>
                                                        <input name="placecode_desc" type="text" size="40" required>
                                                    </p>
                                                    <p align="left">
                                                        <strong>Status:</strong><br>
                                                        <input name="placecode_status" type="radio" value="Active" checked> Active
                                                        <input name="placecode_status" type="radio" value="Inactive"> Inactive
                                                    </p>
                                                    <p align="left">
                                                        <strong>Code:</strong><br>
                                                        Enter your script or embed code here<br>
                                                        <textarea name="placecode_code" cols="40" rows="5" required></textarea>
                                                    </p>
                                                    <p align="left">
                                                        <input type="submit" name="submit" value="Create placecode">
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