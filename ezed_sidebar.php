<?php
require_once('ezed_auth.php');

try {
    // Get all needed database info
    $sidebar_pages = $db->query("SELECT * FROM pages WHERE editable = 'Y' ORDER BY page_id");
    
    // Get galleries
    $galleries = $db->query("SELECT * FROM gallery_info");
    $num_galleries = $galleries->num_rows;
    
    // Check schedules
    $schedules = $db->query("SELECT DISTINCT schedule_no FROM page_schedule");
    $totalSchedules = $schedules->num_rows;
    
    // Get site info
    $site_info = $db->query("SELECT * FROM site")->fetch_assoc();
    $placecodes = strtoupper($site_info['placecodes']);
    $page_tag = strtoupper($site_info['pagetag']);
    $page_script = strtoupper($site_info['pagescript']);
    $my_account = strtoupper($site_info['myaccount']);
    
} catch (Exception $e) {
    handleError('Database Error', $e->getMessage());
}
?>

<td width="12%" align="center" valign="top">
    <table width="189" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td class="sidenav" bgcolor="#DFF1FF" height="40px">
                <div class="sidenavtext" style="margin-left:10px;border-bottom:thin;">
                    <a href="ezed_admin.php">Main Menu</a>
                </div>
            </td>
        </tr>
        <tr>
            <td class="sidenav" height="40px">
                <div class="sidenavtext" style="margin-left:5px; overflow:hidden; width:170px;">
                    <select style="width:190px;" class="sidenavtext" name="page_id" onChange="pageChange(this);">
                        <option value="">Select a page to edit</option>
                        <?php while($page = $sidebar_pages->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($page['page_id']) ?>">
                                <?= htmlspecialchars($page['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </td>
        </tr>
<tr>
            <td class="sidenav" height="40px">
                <div class="sidenavtext" align="left" style="margin-left:10px;border-bottom:thin;">
                    <a href="ezed_file_management.php">Manage Files</a>
                </div>
            </td>
        </tr>
        <tr>
            <td class="sidenav" height="40px">
                <div class="sidenavtext" align="left" style="margin-left:10px;border-bottom:thin;">
                    <a href="ezed_image_management.php">Manage Images</a>
                </div>
            </td>
        </tr>
        <?php if($totalSchedules > 0): ?>
            <tr>
                <td class="sidenav" height="40px">
                    <div class="sidenavtext" align="left" style="margin-left:10px;border-bottom:thin;">
                        <a href="ezed_schedule_management.php">Manage Schedules</a>
                    </div>
                </td>
            </tr>
        <?php endif; ?>
        
        <?php if($placecodes === 'Y' && $_SESSION['MM_UserGroup'] >= 2): ?>
            <tr>
                <td class="sidenav" height="40px">
                    <div align="left" style="margin-left:10px;border-bottom:thin;">
                        <span class="sidenavtext">
                            <a href="ezed_placecode_management.php">Manage Placecodes</a>
                        </span>
                        <span class="text">
                            <a style="text-decoration:none;" href="#" title="Go here to add code from external sources (Javascript, iframes, etc) into a page">(?)</a>
                        </span>
                    </div>
                </td>
            </tr>
        <?php endif; ?>
        <?php if(isset($page_id)): // Only show these items on the content editor page ?>
            <tr>
                <td class="sidenav" height="40px">
                    <div class="sidenavtext" align="left" style="margin-left:10px;border-bottom:thin;">
                        <a href="ezed_backup.php?page_id=<?= htmlspecialchars($page_id) ?>">View page backups</a>
                    </div>
                </td>
            </tr>
            
        <?php endif; ?>
        <?php if($num_galleries > 0): ?>
            <tr>
                <td class="sidenav" height="40px">
                    <div align="left" style="margin-left:10px;border-bottom:thin;">
                        <span class="sidenavtext">Manage Photo Galleries</span><br>
                        <?php 
                        $galleries->data_seek(0);
                        while($gallery = $galleries->fetch_assoc()): 
                        ?>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <a href="ezed_gallery_management.php?gallery=<?= htmlspecialchars($gallery['gallery_type']) ?>">
                                <span class="text"><?= htmlspecialchars($gallery['gallery_name']) ?></span>
                            </a><br>
                        <?php endwhile; ?>
                    </div>
                </td>
            </tr>
        <?php endif; ?>
        
        <?php if($page_tag === 'Y' || $_SESSION['MM_UserGroup'] == 3): ?>
            <tr>
                <td class="sidenav" height="40px">
                    <div align="left" style="margin-left:10px;border-bottom:thin;">
                        <span class="sidenavtext">
                            <a href="ezed_page_tag_editor.php">Page tag editor</a>
                        </span>
                        <span class="text">
                            <a style="text-decoration:none;" href="#" title="Use this to update the page Title, Keyword and Description tags">(?)</a>
                        </span>
                    </div>
                </td>
            </tr>
        <?php endif; ?>
        
        <?php if($page_script === 'Y' || $_SESSION['MM_UserGroup'] == 3): ?>
            <tr>
                <td class="sidenav" height="40px">
                    <div align="left" style="margin-left:10px;border-bottom:thin;">
                        <span class="sidenavtext">
                            <a href="ezed_script_management.php">Page script editor</a>
                        </span>
                        <span class="text">
                            <a style="text-decoration:none;" href="#" title="Use this to add or update scripts in the header or footer of a page">(?)</a>
                        </span>
                    </div>
                </td>
            </tr>
        <?php endif; ?>
        
        <?php if($my_account === 'Y' || $_SESSION['MM_UserGroup'] == 3): ?>
            <tr>
                <td class="sidenav" height="40px">
                    <div class="sidenavtext" align="left" style="margin-left:10px;border-bottom:thin;">
                        <a href="ezed_my_account.php">My Account</a>
                    </div>
                </td>
            </tr>
        <?php endif; ?>
        
        <?php if($_SESSION['MM_UserGroup'] == 3): ?>
            <tr>
                <td class="sidenav" height="40px">
                    <div class="sidenavtext" align="left" style="margin-left:10px;border-bottom:thin;">
                        <a href="ezed_site_admin.php">Site Administration</a>
                    </div>
                </td>
            </tr>
        <?php endif; ?>
        
        <tr>
            <td class="sidenav" height="40px">
                <div class="sidenavtext" align="left" style="margin-left:10px;border-bottom:thin;">
                    <a href="<?= $logoutAction ?>">Logout</a>
                </div>
            </td>
        </tr>
		<?php if(isset($page_id)): // Only show these items on the content editor page ?>
            <tr>
    <td class="sidenav" height="40px" bgcolor="#DFF1FF">
        <div align="left" style="margin-left:10px;border-bottom:thin;">
            <div id="helpContent">Loading help content...</div>
    <script>
        fetch('https://www.studioofdance.com/s2e_support/content_page_help.php')
            .then(response => response.text())
            .then(data => {
                document.getElementById('helpContent').innerHTML = data;
            })
            .catch(error => {
                document.getElementById('helpContent').innerHTML = 'Help content currently unavailable. Please try again later.';
                console.error('Error loading help content:', error);
            });
    </script>
        </div>
    </td>
</tr>
        <?php endif; ?>
    </table>
</td>

<?php
// Clean up resources
if (isset($sidebar_pages)) {
    $sidebar_pages->free();
}
if (isset($galleries)) {
    $galleries->free();
}
if (isset($schedules)) {
    $schedules->free();
}
?>