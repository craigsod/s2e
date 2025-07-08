<?php
require_once('ezed_auth.php');

try {
    // No additional database queries needed for this page
    // The sidebar.php will handle all the navigation data
} catch (Exception $e) {
    handleError('Image Management Error', $e->getMessage());
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
                                            <td width="173" height="50px">
                                                <p align="left" class="subhead">Image Management</p>
                                            </td>
                                            <td width="795" class="sidenavtext">
                                                <div align="left">
                                                    <a href="https://<?= htmlspecialchars($_SESSION['URL']) ?>" target="_blank">View website in new window</a>
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
                                    <div align="left">
                                        <div id="ckfinder1"></div>
                                        <script src="/edit/ckeditor_4_3/ckfinder/ckfinder.js"></script>
                                        <script>
                                            CKFinder.widget('ckfinder1', {
                                                height: 600,
                                                removeModules: 'Maximize',
                                                rememberLastFolder: false,
                                                startupPath: 'Images:/',
                                                resourceType: 'Images',
                                                plugins: [
                                                    '../ckfinder/samples/plugins/StatusBarInfo/StatusBarInfo'
                                                ]
                                            });
                                        </script>
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