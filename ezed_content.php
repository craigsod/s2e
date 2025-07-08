<?php
require_once('ezed_auth.php');

// Function to detect Bootstrap in HTML content
function detectBootstrap($filePath) {
    if (!file_exists($filePath)) {
        return false;
    }
    
    $content = @file_get_contents($filePath);
    if ($content === false) {
        return false;
    }
    
    // Check for Bootstrap references in CSS/JS links
    $hasBootstrap = (
        preg_match('/bootstrap.*\.css/i', $content) || 
        preg_match('/bootstrap.*\.js/i', $content) || 
        preg_match('/cdn\.jsdelivr\.net\/npm\/bootstrap/i', $content) ||
        preg_match('/cdnjs\.cloudflare\.com\/ajax\/libs\/bootstrap/i', $content) ||
        preg_match('/stackpath\.bootstrapcdn\.com\/bootstrap/i', $content) ||
        preg_match('/maxcdn\.bootstrapcdn\.com\/bootstrap/i', $content)
    );
    
    return $hasBootstrap;
}

try {
    // Get page information
    $page_id = filter_input(INPUT_GET, 'page_id', FILTER_VALIDATE_INT);
    if (!$page_id) {
        throw new Exception('Invalid page ID');
    }
    
    // Get current page info
    $stmt = $db->prepare("SELECT * FROM pages WHERE page_id = ?");
    $stmt->bind_param("i", $page_id);
    $stmt->execute();
    
    // Bind all page info fields
    $stmt->bind_result(
        $page_info_page_id, 
        $page_info_name, 
        $page_info_file, 
        $page_info_title, 
        $page_info_keywords, 
        $page_info_description, 
        $page_info_extra_info,
        $page_info_header_image,
        $page_info_header_file,
        $page_info_footer_file,
        $page_info_editable
    );
    
    $has_page = $stmt->fetch();
    $stmt->close();
    
    if (!$has_page) {
        throw new Exception('Page not found');
    }
    
    // Create page_info array to match old structure
    $page_info = array(
        'page_id' => $page_info_page_id,
        'name' => $page_info_name,
        'file' => $page_info_file,
        'title' => $page_info_title,
        'keywords' => $page_info_keywords,
        'description' => $page_info_description,
        'extra_info' => $page_info_extra_info,
        'header_image' => $page_info_header_image,
        'header_file' => $page_info_header_file ?: 'header.php',
        'footer_file' => $page_info_footer_file ?: 'footer.php',
        'editable' => $page_info_editable
    );
    
    // Safely store page info in session
    $_SESSION['page_name'] = $page_info_name;
    $_SESSION['file_name'] = $page_info_file;
    $_SESSION['page_title'] = $page_info_title;
    $_SESSION['page_keywords'] = $page_info_keywords;
    $_SESSION['page_description'] = $page_info_description;
    $_SESSION['extra_info'] = $page_info_extra_info;
    $_SESSION['header_image'] = $page_info_header_image;
    $_SESSION['header_file'] = $page_info_header_file ?: 'header.php';
    $_SESSION['footer_file'] = $page_info_footer_file ?: 'footer.php';
    
    // Get page content - first check for temporary content
    $temp_content_stmt = $db->prepare("SELECT content_id, page_id, contents, created, updated, status FROM content WHERE status = 'T' AND page_id = ?");
    if (!$temp_content_stmt) {
        throw new Exception('Failed to prepare temporary content statement: ' . $db->error);
    }
    $temp_content_stmt->bind_param("i", $page_id);
    $temp_content_stmt->execute();
    
    // Bind results for temporary content
    $temp_content_stmt->bind_result(
        $tmp_content_id, 
        $tmp_page_id, 
        $tmp_contents, 
        $tmp_created, 
        $tmp_updated, 
        $tmp_status
    );
    
    // Check if we have temporary content
    $has_temp_content = $temp_content_stmt->fetch();
    $temp_content_stmt->close();
    
    if ($has_temp_content) {
        // If temp content exists, use it
        $content_data = array(
            'content_id' => $tmp_content_id,
            'page_id' => $tmp_page_id,
            'contents' => $tmp_contents,
            'created' => $tmp_created,
            'updated' => $tmp_updated,
            'status' => $tmp_status
        );
        $editcontent = htmlspecialchars(stripslashes($tmp_contents), ENT_QUOTES, 'UTF-8');
    } else {
        // If no temporary content, get active content
        $active_content_stmt = $db->prepare("SELECT content_id, page_id, contents, created, updated, status FROM content WHERE status = 'A' AND page_id = ?");
        if (!$active_content_stmt) {
            throw new Exception('Failed to prepare active content statement: ' . $db->error);
        }
        $active_content_stmt->bind_param("i", $page_id);
        $active_content_stmt->execute();
        
        // Bind results for active content
        $active_content_stmt->bind_result(
            $act_content_id, 
            $act_page_id, 
            $act_contents, 
            $act_created, 
            $act_updated, 
            $act_status
        );
        
        // Check if we have active content
        $has_active_content = $active_content_stmt->fetch();
        $active_content_stmt->close();
        
        if ($has_active_content) {
            $content_data = array(
                'content_id' => $act_content_id,
                'page_id' => $act_page_id,
                'contents' => $act_contents,
                'created' => $act_created,
                'updated' => $act_updated,
                'status' => $act_status
            );
            $editcontent = htmlspecialchars(stripslashes($act_contents), ENT_QUOTES, 'UTF-8');
        } else {
            // No content found at all
            $content_data = array();
            $editcontent = '';
        }
    }
    
} catch (Exception $e) {
    handleError('Content Editor Error', $e->getMessage());
}

// Detect Bootstrap in index.html
$indexPath = $_SERVER['DOCUMENT_ROOT'] . "/index.html";
if (!file_exists($indexPath)) {
    // Try index.htm if index.html doesn't exist
    $indexPath = $_SERVER['DOCUMENT_ROOT'] . "/index.htm";
}

// Use the detectBootstrap function to check for Bootstrap
$hasBootstrap = detectBootstrap($indexPath);

// Store the result in session for use in script
$_SESSION['has_bootstrap'] = $hasBootstrap;

// Prepare user group and bootstrap detection for JavaScript
$userGroup = isset($_SESSION['MM_UserGroup']) ? $_SESSION['MM_UserGroup'] : '';
$bootstrapDetected = $hasBootstrap ? 'true' : 'false';

?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($_SESSION['site_name']) ?></title>
    <meta charset="utf-8">
    <script src="ckeditor_4_3/ckeditor.js"></script>
    <script src="https://code.jquery.com/jquery-1.9.1.js"></script>
    <script src="https://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    <link href="css/edit1.css" rel="stylesheet">
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
                                                <p align="left" class="text">
                                                    <span class="subhead">
                                                        <strong>Currently editing: <?= htmlspecialchars($page_info['name']) ?></strong>
                                                    </span>
                                                </p>
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
                                    <?php if($has_temp_content): ?>
                                        <p align="left">
                                            <span class="warning">
                                                &nbsp;&nbsp;This page has unsaved changes.
                                                <br>&nbsp;&nbsp;Click 'Save and publish to web' to publish your changes.
                                            </span>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <!-- Save/Update/Preview Buttons -->
                                    <div align="left" style="padding:5px;">
                                        <table height="30px" width="900" cellpadding="2" cellspacing="0" border="0">
                                            <tr>
                                                <td width="34%">
                                                    <table width="200" align="center">
                                                        <tr>
                                                            <td>
                                                                <div align="center">
                                                                    <a href="#" class="saveButton" data-action="save">Save changes only</a>
                                                                    <span class="text"><br>Do not update on website</span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td width="34%">
                                                    <table align="center">
                                                        <tr>
                                                            <td>
                                                                <div align="center">
                                                                    <a href="#" class="updateButton" data-action="update">Save and publish to web</a>
                                                                    <br>
                                                                    <span class="text">Page is updated on website</span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td width="32%">
                                                    <table width="200" align="center">
                                                        <tr>
                                                            <td>
                                                                <div align="center">
                                                                    <a href="#" class="previewButton" data-action="preview">Preview changes</a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>

                                    <!-- Content Editor Form -->
                                    <form method="POST" name="form1">
                                        <table width="1000">
                                            <tr valign="baseline">
                                                <td>
                                                    <div style="padding-left:5px; overflow:auto;">
                                                        <textarea class="ckeditor" name="content" id="content"><?= $editcontent ?></textarea>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                        <input type="hidden" name="MM_update" value="form1">
                                        <input type="hidden" name="created" value="NOW()">
                                        <input type="hidden" name="content_id" value="<?= htmlspecialchars($content_data['content_id'] ?? '') ?>">
                                    </form>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>
<script>
// Inject PHP variables into JavaScript context
const userGroup = '<?= $userGroup ?>';
const hasBootstrap = <?= $bootstrapDetected ?>;

// Add error handler
CKEDITOR.on('loaderror', function(evt) {
    console.log('CKEditor loader error:', evt);
});

// CKEditor configuration selection
function selectCKEditorConfig() {
    if (userGroup === '3') {
        return 'admin-config.js';
    } else {
        return hasBootstrap ? 'user-bs-config.js' : 'user-config.js';
    }
}

// Initialize CKEditor with dynamic configuration
let editor = CKEDITOR.replace('content', {
    customConfig: selectCKEditorConfig()
});

// Modified saveContent function to ensure proper editor reference
function saveContent(action, target = '_self') {
    if (editor) {
        try {
            editor.updateElement();
            var form = document.forms['form1'];
            form.action = action;
            form.target = target;
            form.submit();
            return false;
        } catch (e) {
            console.error('Error saving content:', e);
            return false;
        }
    } else {
        console.error('Editor not initialized');
        return true;
    }
}

// Update button click handlers
document.querySelector('.saveButton').onclick = function(e) {
    e.preventDefault();
    return saveContent('ezed_content_update.php?page_id=<?= $page_id ?>&status=T');
};

document.querySelector('.updateButton').onclick = function(e) {
    e.preventDefault();
    return saveContent('ezed_content_update.php?page_id=<?= $page_id ?>');
};

document.querySelector('.previewButton').onclick = function(e) {
    e.preventDefault();
    if (editor) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'ezed_preview.php?page_id=<?= $page_id ?>';
        form.target = '_blank';

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'content';
        input.value = editor.getData();
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
    return false;
};

// Keep existing functions
function checkSaved(dest) {
    if (editor && editor.checkDirty()) {
        if (confirm('You have unsaved changes.\n\nClick OK to continue without saving.\n\nClick Cancel to go back and save your changes.')) {
            window.location.href = dest;
        }
        return false;
    }
    window.location.href = dest;
    return false;
}

function pageChange(select) {
    const pageId = select.value;
    if (!pageId) return false;

    if (editor && editor.checkDirty()) {
        if (confirm('You have unsaved changes.\n\nClick OK if you have saved your changes.\n\nClick Cancel to go back and save them.')) {
            window.location.href = 'ezed_content.php?page_id=' + pageId;
        } else {
            select.selectedIndex = 0;
        }
        return false;
    }
    window.location.href = 'ezed_content.php?page_id=' + pageId;
    return false;
}
</script>

<?php if(isset($_SESSION['updatesave']) && $_SESSION['updatesave']): ?>
    <script>
        alert('Your changes have been saved and uploaded.');
    </script>
    <?php $_SESSION['updatesave'] = false; ?>
<?php endif; ?>
</body>
</html>

<?php
// Clean up resources if needed
if (isset($temp_content_stmt)) {
    $temp_content_stmt->close();
}
if (isset($active_content_stmt)) {
    $active_content_stmt->close();
}
?>