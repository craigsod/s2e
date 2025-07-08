<?php
declare(strict_types=1);

require_once('ezed_auth.php');

try {
    // Get all database info needed for the page
    $stmt = $db->prepare("SELECT placecodes, pagetag, pagescript, myaccount FROM site LIMIT 1");
    $stmt->execute();
    $siteInfo = $stmt->get_result()->fetch_assoc();
    
    // Process form submission for site settings
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['changesitevals'])) {
        $updateStmt = $db->prepare("UPDATE site SET 
            placecodes = ?, 
            pagetag = ?, 
            pagescript = ?, 
            myaccount = ?
        ");
        
        $placecode = $_POST['placecode'] ?? 'N';
        $pagetag = $_POST['pagetag'] ?? 'N';
        $pagescript = $_POST['pagescript'] ?? 'N';
        $myaccount = $_POST['myaccount'] ?? 'N';
        
        $updateStmt->bind_param("ssss", 
            $placecode,
            $pagetag,
            $pagescript,
            $myaccount
        );
        
        if (!$updateStmt->execute()) {
            throw new Exception('Failed to update site settings');
        }
        
        // Refresh site info after update
        $siteInfo = [
            'placecodes' => $placecode,
            'pagetag' => $pagetag,
            'pagescript' => $pagescript,
            'myaccount' => $myaccount
        ];
    }
    
    // Get user information
    $stmt = $db->prepare("SELECT username, pwd, email FROM users WHERE user_id = 2");
    $stmt->execute();
    $userInfo = $stmt->get_result()->fetch_assoc();
    
} catch (Exception $e) {
    handleError('Site Admin Error', $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($_SESSION['site_name']) ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="css/edit1.css" rel="stylesheet">
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
                                <td colspan="4">
                                    <table border="0" cellpadding="8" cellspacing="0" width="1135">
                                        <tr>
                                            <td height="50px">
                                                <span class="subhead">Site Administration</span>
                                                <?php if($_SESSION['MM_UserGroup'] == 3): ?>
                                                    <br><span class='warning'>You are logged in as an administrator</span>
                                                <?php endif; ?>
                                            </td>
                                            <td width="97" class="sidenavtext">
                                                <div align="right">
                                                    <a href="https://<?= htmlspecialchars($_SESSION['URL']) ?>" target="_blank">View website</a>
                                                </div>
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
                                    
                                    <!-- Site Settings Form -->
                                    <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                                        <table width="100%" border="1" cellpadding="8" cellspacing="0">
                                            <tr>
                                                <td>
                                                    <p class="subhead">Site Settings</p>
                                                    
                                                    <p class="text">
                                                        <strong>Enable Placecodes:</strong><br>
                                                        <input type="radio" name="placecode" value="Y" <?= $siteInfo['placecodes'] === 'Y' ? 'checked' : '' ?>> Yes
                                                        <input type="radio" name="placecode" value="N" <?= $siteInfo['placecodes'] === 'N' ? 'checked' : '' ?>> No
                                                    </p>
                                                    
                                                    <p class="text">
                                                        <strong>Enable Page Tags:</strong><br>
                                                        <input type="radio" name="pagetag" value="Y" <?= $siteInfo['pagetag'] === 'Y' ? 'checked' : '' ?>> Yes
                                                        <input type="radio" name="pagetag" value="N" <?= $siteInfo['pagetag'] === 'N' ? 'checked' : '' ?>> No
                                                    </p>
                                                    
                                                    <p class="text">
                                                        <strong>Enable Page Scripts:</strong><br>
                                                        <input type="radio" name="pagescript" value="Y" <?= $siteInfo['pagescript'] === 'Y' ? 'checked' : '' ?>> Yes
                                                        <input type="radio" name="pagescript" value="N" <?= $siteInfo['pagescript'] === 'N' ? 'checked' : '' ?>> No
                                                    </p>
                                                    
                                                    <p class="text">
                                                        <strong>Enable My Account:</strong><br>
                                                        <input type="radio" name="myaccount" value="Y" <?= $siteInfo['myaccount'] === 'Y' ? 'checked' : '' ?>> Yes
                                                        <input type="radio" name="myaccount" value="N" <?= $siteInfo['myaccount'] === 'N' ? 'checked' : '' ?>> No
                                                    </p>
                                                    
                                                    <p>
                                                        <input type="submit" name="changesitevals" value="Save Changes" class="button">
                                                    </p>
													 <p align="left" class='text'><a href="ezed_auto_build_form.php">Auto Build all pages</a></p>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                    
                                    <!-- User Information Display -->
                                    <table width="100%" border="1" cellpadding="8" cellspacing="0">
                                        <tr>
                                            <td>
                                                <p class="subhead">User Information</p>
                                                <p class="text">
                                                    <strong>Username:</strong> <?= htmlspecialchars($userInfo['username']) ?><br>
                                                    <strong>Email:</strong> <?= htmlspecialchars($userInfo['email']) ?>
                                                </p>
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