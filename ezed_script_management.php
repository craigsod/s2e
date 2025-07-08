<?php 
require_once('../../Connections/studioAdmin_i.php'); 
include_once('ezed_create_HTML_function.php');

function log_error($error_sub,$error_msg)
{
	mail('admin@studioofdancehosting.com', $error_sub,$error_msg);
}

//initialize the session
if (!isset($_SESSION)) {
  session_start();
}

$_SESSION['header_file'] = NULL;
$_SESSION['footer_file'] = NULL;
unset($_SESSION['header_file']);
unset($_SESSION['footer_file']);

// ** Logout the current user. **
$logoutAction = $_SERVER['PHP_SELF']."?doLogout=true";
if ((isset($_SERVER['QUERY_STRING'])) && ($_SERVER['QUERY_STRING'] != "")){
  $logoutAction .="&". htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){

	
  //to fully log out a visitor we need to clear the session varialbles
  $_SESSION['MM_Username'] = NULL;
  $_SESSION['MM_UserGroup'] = NULL;
  $_SESSION['PrevUrl'] = NULL;
  unset($_SESSION['MM_Username']);
  unset($_SESSION['MM_UserGroup']);
  unset($_SESSION['PrevUrl']);
	
  $logoutGoTo = "index.php";
  if ($logoutGoTo) {
    header("Location: $logoutGoTo");
    exit;
  }
}

$MM_authorizedUsers = "3,1";
$MM_donotCheckaccess = "false";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && false) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}
$MM_restrictGoTo = "index.php";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($QUERY_STRING) && strlen($QUERY_STRING) > 0) 
  $MM_referrer .= "?" . $QUERY_STRING;
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}


function db_error_handle()
{
	mail('admin@studioofdancehosting.com', 'database connection error', 'An error occured in the index.php file');
	header("Location: db_error.htm");
}

// Establish database connections
mysqli_select_db($studioAdmin, $database_studioAdmin);


// ********************************************************************************
// GET PAGE ID from URL
//
if(isset($_GET['page_id']) && $_GET['page_id'] <>'') {
	$page_id = $_GET['page_id'];
	if(isset($_SESSION['script_page_id']) && $_SESSION['script_page_id'] <> $page_id) {
		$_SESSION['script_page_id'] = $page_id;
	} else {
		$_SESSION['script_page_id'] = $page_id;
	}
	// Query scripts table for scripts on this page
	$query_scripts = "SELECT * FROM scripts WHERE page_id = '$page_id'";
	$get_scripts = mysqli_query($studioAdmin, $query_scripts);
	
	// Query page name
	$query_page_name = "SELECT * FROM pages WHERE page_id = '$page_id'";
	$get_page_name = mysqli_query($studioAdmin, $query_page_name);
	$row_page_name = mysqli_fetch_assoc($get_page_name);
	$page_name = $row_page_name['name'];
}




//*****************************************************************************
// ADD NEW script
//
if(isset($_POST['script_desc']) && $_POST['script_desc'] <>'' && $_POST['script_code'] <>'') {

 	if($page_id == '') {
		header("Location: ezed_admin.php");
		exit();
	}
	$script_desc = $_POST['script_desc'];
	$script_status = $_POST['script_status'];
	$location = $_POST['location'];
	$script_code = htmlentities($_POST['script_code'], ENT_QUOTES, "UTF-8");


	$insert_query = "INSERT INTO scripts (page_id,script_description,script_code,location,status) VALUES ('$page_id','$script_desc','$script_code','$location','$script_status')";
	mysqli_query($studioAdmin, $insert_query);
	
	// Build the page with new script
	createHTMLfile($page_id, $studioAdmin,$database_studioAdmin,$studioAdmin,$database_studioAdmin);
	
	// Reload this page to clear form values
	header("Location: ezed_script_management.php?page_id=" . $page_id);
}

// **********************************************************
// Get site information from SITE table
// **********************************************************

$query_getSiteInfo = "SELECT * FROM site";
$getSiteInfo = mysqli_query($studioAdmin, $query_getSiteInfo);
$row_getSiteInfo = mysqli_fetch_assoc($getSiteInfo);


// Set form action to call itself
$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

// ********************************************************************
// CHECK FOR ANY SCHEDULES ON THIS SITE
//
$query_getSchedules = "SELECT * FROM page_schedule GROUP BY schedule_no";
$getSchedules = mysqli_query($studioAdmin, $query_getSchedules);
$totalSchedules = mysqli_num_rows($getSchedules);


// ********************************************************************
// Query gallery_info table to get all galleries for this site
//
$query_gallery = "SELECT * FROM gallery_info";
$get_galleries = mysqli_query($studioAdmin, $query_gallery);
$num_galleries = mysqli_num_rows($get_galleries);

//*********************************************************************
//  GET ALL THE PAGES TO POPULATE THE SIDEBAR MENU
//********************************************************************
$query_getSidePages = "SELECT * FROM pages WHERE editable = 'Y' ORDER BY page_id";
$getSidePages = mysqli_query($studioAdmin, $query_getSidePages);
//$row_getPages = mysql_fetch_assoc($getPages);

//*********************************************************************
//  GET ALL THE PAGES TO POPULATE THE SCRIPT EDITOR DROPDOWN MENU
//********************************************************************
$query_ScriptPages = "SELECT * FROM pages WHERE editable = 'Y' ORDER BY page_id";
$getScriptPages = mysqli_query($studioAdmin, $query_ScriptPages);
//$row_getPages = mysql_fetch_assoc($getPages);

//*********************************************************************
// GET scriptS
//
if(!isset($_GET['page_id']) || $_GET['page_id'] =='') {
	$_SESSION['script_page_id'] = NULL;
	unset($_SESSION['script_page_id']);
	$page_id = '';
	$num_scripts = 0;
} else {
	$query_scripts = "SELECT * FROM scripts WHERE page_id = '$page_id'";
	$get_scripts = mysqli_query($studioAdmin, $query_scripts);
	$num_scripts = mysqli_num_rows($get_scripts);
}

// Record action
function get_url($user, $site, $date, $action,$url) {
  $update_log = "http://www.studioofdance.com/db/update_log.php?user=" . $user . "&site=" . $site . "&date=" . $date . "&action=" . $action . "&url=" . $url;
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $update_log);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $response = curl_exec($ch);
  curl_close($ch);
}

$user = urlencode($_SESSION['MM_Username']);
$site = urlencode($_SESSION['site_name']);
$date = urlencode(date("n/j/Y g:i:s a"));
$url = $_SESSION['URL'];
$action = "Script management page";
$action = urlencode($action);
get_url($user,$site,$date,$action,$url);

?><!DOCTYPE html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8" />
<title><?php echo $_SESSION['site_name']; ?></title>


<style type="text/css">
<!--
.widebox {
  width: 500px;
  }
   .sidenavmain {
	border-left-width:1px; 
	border-left-color:#CCCCCC; 
	border-left-style:solid;
	border-bottom-width:1px; 
	border-bottom-color:#CCCCCC; 
	border-bottom-style:solid;
}
 .sidenav {
	border-left-width:1px; 
	border-left-color:#CCCCCC; 
	border-left-style:solid;
	border-bottom-width:1px; 
	border-bottom-color:#CCCCCC; 
	border-bottom-style:solid;
	border-right-width:1px; 
	border-right-color:#CCCCCC; 
	border-right-style:solid;
}
.sidenavtext {
	font-family: "Franklin Gothic Medium Cond", Verdana, "Arial Narrow";
	font-weight: bold;
	color: #666666;
	font-size: 12px;
	text-decoration:none;
}
.sidenavtext a:link {
	font-family: "Franklin Gothic Medium Cond", Verdana, "Arial Narrow";
	font-weight: bold;
	color: #666666;
	font-size: 12px;
	text-decoration:none;
}
.sidenavtext a:visited {
	font-family: "Franklin Gothic Medium Cond", Verdana, "Arial Narrow";
	font-weight: bold;
	color: #666666;
	font-size: 12px;
	text-decoration:none;
}

.sidenavtext a:hover {
	font-family: "Franklin Gothic Medium Cond", Verdana, "Arial Narrow";
	font-weight: bold;
	color: #666666;
	font-size: 12px;
	text-decoration:underline;
}
.sidenavtext a:active{
	font-family: "Franklin Gothic Medium Cond", Verdana, "Arial Narrow";
	font-weight: bold;
	color: #666666;
	font-size: 12px;
	text-decoration:none;
}
-->
</style>
<script language="JavaScript" type="text/javascript">
<!--
function ChangePage(Opt_List)
{
var page_id = Opt_List.options[Opt_List.selectedIndex].value;
page_id = page_id * 1;
window.location="ezed_content.php?page_id=" + page_id ;
}
//-->
</script>
<script language="JavaScript" type="text/javascript">
<!--
function EditPageScript(Page_List)
{
var pages_id = Page_List.options[Page_List.selectedIndex].value;
pages_id = pages_id * 1;
window.location="ezed_script_management.php?page_id=" + pages_id ;
}
//-->
</script>
<link href="css/edit1.css" rel="stylesheet" type="text/css">
</head>

<body bgcolor="FFFFFF">
<table width="100%" border="1" cellpadding="0" cellspacing="0" bordercolor="#000000" bgcolor="#FFFFFF">
  <tr> 
    <td valign="top" bordercolor="#FFFFFF"><div align="center"> 
        <table width="100%" border="0" cellpadding="0" cellspacing="0" bordercolor="#999999">
          <tr bgcolor="#FFFFFF">
            <td colspan="4"><table border="0" cellpadding="8" cellspacing="0" width="1000">
                  
                <tr> 
                  <td height="50px"><p align="left" class="subhead">Script Management</p></td>
                  <td>&nbsp;</td>
                </tr>
            </table></td> 
          </tr>
          <tr>
            <td width="12%" align="center" valign="top">
			  <table width="189" cellspacing="0" cellpadding="0" border="0">
			  <tr><td class="sidenav" bgcolor="#DFF1FF" height="40px" ><div class="sidenavtext" style="margin-left:10px;border-bottom:thin;"><a href="ezed_admin.php">Main Menu </a></div></td></tr>
			 <tr>
			   <td height="40px" bgcolor="#E6E6E6" class="sidenav"><div style="margin-left:5px; overflow:hidden; width:170px;"><select style="width:190px;" class="buttons" name=page_id onChange="ChangePage(this);">
  	<option value="">Select a page to edit</option>
    <?php
				  	while($row_getPages = mysqli_fetch_assoc($getSidePages))
					{
						echo "<option value=" . $row_getPages['page_id'] . ">" . $row_getPages['name'] . "</option>";
					}
					 mysqli_data_seek($getPages,0);
					?>
  </select></div></td>
			 </tr>
			 <tr><td class="sidenav" height="40px"><div class="sidenavtext" align="left" style="margin-left:10px;border-bottom:thin;"><a href="ezed_file_management.php">Manage Files </a></div></td>
			 </tr>
			 <tr><td class="sidenav" height="40px" ><div class="sidenavtext" align="left" style="margin-left:10px;border-bottom:thin;"><a href="ezed_image_management.php">Manage Images</a></div></td></tr>
			 <?php 
					if($totalSchedules != 0) { ?> <tr><td class="sidenav" height="40px" ><div class="sidenavtext" align="left" style="margin-left:10px;border-bottom:thin;"><a href="ezed_schedule_management.php">Manage Schedules</a></div></td></tr><?php } ?>
			 <tr><td class="sidenav" height="40px" ><div align="left" style="margin-left:10px;border-bottom:thin;"><span class="sidenavtext"><a href="ezed_placecode_management.php">Manage Placecodes</a></span><span class="text"> <a style="text-decoration:none;" href="#" title="Go here to add code from external sources (Javascript, iframes, etc) into a page">(?)</a></span></div></td></tr>
			 <?php if($num_galleries >0) { echo "<tr><td class='sidenav' height='40px' ><div  align='left' style='margin-left:10px;border-bottom:thin;'><span class='sidenavtext'>Manage Photo Galleries</span><br>"; while($gallery = mysqli_fetch_assoc($get_galleries)) { echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href='ezed_gallery_management.php?gallery=" . $gallery['gallery_type'] . "'><span class='text'>" . $gallery['gallery_name'] . "</span></a><br>"; } echo "</div></td></tr>"; }?>
			 <tr><td class="sidenav" height="40px" ><div  align="left" style="margin-left:10px;border-bottom:thin;"><span class="sidenavtext"><a href="ezed_page_tag_editor.php">Page tag editor</a></span><span class="text"> <a style="text-decoration:none;" href="#" title="Use this to update the page Title, Keyword and Description tags">(?)</a></span></div></td></tr>
			 <tr><td class="sidenav" height="40px" ><div  align="left" style="margin-left:10px;border-bottom:thin;"><span class="sidenavtext"><a href="ezed_script_management.php">Page script editor</a></span><span class="text"> <a style="text-decoration:none;" href="#" title="Use this to add or update scripts in the header or footer of a page">(?)</a></span></div></td></tr>
			 <tr><td class="sidenav" height="40px" ><div class="sidenavtext" align="left" style="margin-left:10px;border-bottom:thin;"><a href="ezed_my_account.php">My Account</a></div></td></tr>
			 <?php if($_SESSION['MM_UserGroup'] == 3) { ?><tr><td class="sidenav" height="40px" ><div class="sidenavtext" align="left" style="margin-left:10px;border-bottom:thin;"><a href="ezed_site_admin.php">Site Administration </a></div></td></tr> <?php } ?>
			 <tr><td class="sidenav" height="40px"><div class="sidenavtext" align="left" style="margin-left:10px;border-bottom:thin;"><a onClick="window.location='<?php echo $logoutAction ?>'" href="#">Logout</a></div></td></tr>
		    </table></td> 
            <td width="0%" valign="top" bgcolor="#DFF1FF">&nbsp;</td>
            <td width="88%" valign="top"><div style="background-color:#DFF1FF; height:5px;"></div>			  <table width="100%" border="1" cellpadding="8" cellspacing="0">
			    <tr>
			      <td colspan="2" valign="top">
				  <form action="ezed_script_management.php" method="post">
				  <select onChange="EditPageScript(this);">
  	<option value="">Select a page</option>
    <?php
				  	while($row_getScriptPages = mysqli_fetch_assoc($getScriptPages))
					{
						echo "<option value=" . $row_getScriptPages['page_id'] . ">" . $row_getScriptPages['name'] . "</option>";
					}
					?>
  </select></form>				  </td>
		        </tr>
			    <tr><td width="64%" valign="top"><table width="100%" border="1" cellpadding="8" cellspacing="0">
			    <tr>
			      <td valign="top">
				  <?php if($num_scripts >=1) { ?>
			   		 <p align="left" class="text"><strong>Script management for: <span class="buttons"><?php echo strtoupper($page_name); ?></span></strong></p>
				
					<table width="98%" border="1" cellpadding="8" cellspacing="0" bordercolor="#CCCCCC">
					  <tr>
					  <td class="text"><div align="left"><strong>Script Description</strong></div></td>
					  <td width="20%" class="text"><div align="left"><strong>Status</strong></div></td>
					<td width="9%" class="text">&nbsp;</td>
					<td width="9%" class="text">&nbsp;</td>
					</tr>
					<?php while($row_scripts = mysqli_fetch_assoc($get_scripts)) {
						echo "<tr><td class='text'><div align='left'>" . $row_scripts['script_description'] . "</div></td><td class='text'><div align='left'>" . $row_scripts['status'] . "</div></td><td class='text'><div align='left'><a href='ezed_script_edit.php?id=" . $row_scripts['script_id'] . "&page=" . $page_id . "'>Edit</a></div></td><td class='text'><div align='left'><a href='ezed_script_delete.php?id=" . $row_scripts['script_id'] . "&page=" . $page_id . "'>Delete</a></div></td></tr>";
					} ?>
					<tr>
					  <td class="text" colspan="4">&nbsp;</td>
					</tr>
					</table>
				<?php } elseif($num_scripts ==0 && $page_id <> '') {
					echo  '<p>&nbsp;</p><p align="left" class="text"><strong>There are no scripts for this page yet. Use the Add Script process to the right.</strong></p><p>&nbsp;</p><p>&nbsp;</p>';
					} else {
					echo  '<p>&nbsp;</p><p align="left" class="text"><strong>Select a page from the drop-down above</strong></p><p>&nbsp;</p><p>&nbsp;</p>';
					}	 ?>
				</td></tr></table>
			    <td width="36%"><table width="100%">
			      <tr><td width="31%" valign="top" class="text">
				  <?php 
				  if($num_scripts ==0 && $page_id == '') {
				  	echo "<p>&nbsp;</p>";
				  } else { ?>
					  <p align="left" class="text"><strong>Add Script to <span class="buttons"><?php echo strtoupper($page_name); ?></span></strong></p>
						  <form method="post" action="">
					  <p align="left"><strong>Script description:</strong><br>
						<input name="script_desc" type="text" size="40" >
					  </p>
					  <p align="left"><strong>Script status:</strong> 
						<input name="script_status" type="radio" value="Active" checked>
						Active
						<input name="script_status" type="radio" value="Inactive">
						Inactive</p>
					  <p align="left"><strong>Script code:</strong><br>
						<textarea name="script_code" cols="40" rows="5" id="script_code"></textarea>
					  </p>
					  <p align="left">Location: 
						<input name="location" type="radio" value="Header" checked>
						Header 
						<input name="location" type="radio" value="Footer">
					  Footer</p>
					  <p align="left">
						<input type="submit" name="submit" value="Create script">	
						</p>
					</form>			
					<?php } ?>
				</td></tr></table></td></tr>
				</table></td>
          </tr>
        </table>
          
        </div>    </td>
  </tr>
</table>
</body>
</html>