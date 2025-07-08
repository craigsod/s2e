<?php
require_once('ezed_auth.php');

// Enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Get site info for the main page - using bind_result instead of get_result
    $stmt = $db->prepare("SELECT studio_name, placecodes, pagetag, pagescript, myaccount, URL FROM site");
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $db->error);
    }
    
    $stmt->execute();
    
    // Bind variables to the prepared statement
    $stmt->bind_result($studio_name, $placecodes, $pagetag, $pagescript, $myaccount, $url);
    
    // Fetch values
    $stmt->fetch();
    
    // Store in an array for consistent usage with the rest of the code
    $row_getSiteInfo = [
        'studio_name' => $studio_name,
        'placecodes' => $placecodes,
        'pagetag' => $pagetag,
        'pagescript' => $pagescript,
        'myaccount' => $myaccount,
        'URL' => $url
    ];
    
    $stmt->close();
    
    // Set session variables
    $_SESSION['site_name'] = $studio_name;
    $_SESSION['URL'] = $url;
    
} catch (Exception $e) {
    // Display error instead of redirecting for debugging
    echo "Error: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($_SESSION['site_name']) ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
    <script src="https://code.jquery.com/jquery-1.9.1.js"></script>
    <script src="https://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    <link href="css/edit1.css" rel="stylesheet" type="text/css">
    <!-- Commented out potentially problematic include -->
    <!-- <?php //include_once("ezed_modal_code.php"); ?> -->
</head>
<body bgcolor="443B34">
    <div align="center">
        <table width="1170" height="478" border="1" cellpadding="0" cellspacing="0" bordercolor="#000000" bgcolor="#FFFFFF">
            <tr> 
                <td valign="top" bordercolor="#FFFFFF">
                    <div align="center"> 
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" bordercolor="#999999">
                            <tr bgcolor="#FFFFFF">
                                <td colspan="3">
                                    <table border="0" cellpadding="8" cellspacing="0" width="1135">
                                        <tr> 
                                            <td width="1006" height="50px">
                                                <span class="subhead">Simple2Edit Administration Menu for: </span> 
                                                <span class="subhead"><strong><?= htmlspecialchars($_SESSION['site_name']) ?></strong></span>
                                                <?php if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup'] == 3): ?>
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
                                    <div style="background-color:#DFF1FF; height:5px;">
                                        <table width="100%" border="1" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td>
                                                    <!-- Replaced fetch with static content -->
                                                    <div id="helpContent">Loading help content...</div>
<script>
 // Use our local proxy instead
const url = 's2e_proxy.php?type=main';

console.log('Attempting to fetch content from:', url);

// Add headers to the fetch request
fetch(url, {
    method: 'GET',
    headers: {
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Content-Type': 'text/html; charset=utf-8'
    },
    mode: 'cors',
    cache: 'no-cache'
})
.then(response => {
    console.log('Response status:', response.status);
    if (!response.ok) {
        throw new Error('Network response was not OK: ' + response.status);
    }
    return response.text();
})
.then(data => {
    console.log('Data received, length:', data.length);
    document.getElementById('helpContent').innerHTML = data;
})
.catch(error => {
    console.error('Error loading help content:', error);
    document.getElementById('helpContent').innerHTML = 
        'Help content currently unavailable. Please try again later.<br>' +
        'Error: ' + error.message;
});
</script>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
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