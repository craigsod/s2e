<?php include_once('../../Connections/studioAdmin.php'); ?>
<?php
function log_error($error_sub,$error_msg)
{
	mail('admin@studioofdancehosting.com', $error_sub,$error_msg);
}

//initialize the session
if (!isset($_SESSION)) {
  session_start();
}

// Create logging function
function get_url($user, $site, $date, $action, $url) {
  $update_log = "http://www.studioofdance.com/db/update_log.php?user=" . $user . "&site=" . $site . "&date=" . $date . "&action=" . $action . "&url=" . $url;
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $update_log);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $response = curl_exec($ch);
  curl_close($ch);
}

//*****************************************************************
// Unique code for various scipts
// ****************************************************************

// Clear header/footer so page is created with correct files
// UNIQUE to ezed_content.php file
$_SESSION['header_file'] = NULL;
$_SESSION['footer_file'] = NULL;
unset($_SESSION['header_file']);
unset($_SESSION['footer_file']);

// Get domain name from session var and strip any leading characters
// UNIQUE to file management 
$domain = $_SESSION['URL'];
$www_position = strpos($domain, '.');
$stripped_domain = substr($domain,$www_position+1);

// *************************************************************************
// GET GALLERY ID FROM URL
//
if(isset($_GET['gallery']) && $_GET['gallery'] <> '') {
	$gallerydir = $_GET['gallery'];
}

//*********************************************************************
//  GET ALL THE PAGES TO POPULATE THE SCRIPT EDITOR DROPDOWN MENU
//********************************************************************
$query_TagPages = "SELECT * FROM pages WHERE editable = 'Y' ORDER BY page_id";
$getTagPages = mysql_query($query_TagPages, $studioAdmin);
//$row_getPages = mysql_fetch_assoc($getPages);

// *********************************************
// UNIQUE to placecode edit page
$placecode_id = $_GET['id'];

$query_placecode = "SELECT * FROM placecodes WHERE placecode_id = '$placecode_id'";
$get_placecode = mysql_query($query_placecode, $studioAdmin) or die(mysql_error());
$row_getplacecode = mysql_fetch_assoc($get_placecode);
$placecode = $row_getplacecode['placecode'];

// Save placecode changes
if(isset($_POST['submit']) && $_POST['submit'] <> "" && isset($_POST['placeid'])) {

	$placeid = $_POST['placeid'];
	$placecode_status = $_POST['placecode_status'];
	$placecode_desc = $_POST['placecode_desc'];
	$placecode_code = htmlentities($_POST['placecode_code'], ENT_QUOTES, "UTF-8");
	$schedule = $_POST['schedule'];
	$registration = $_POST['registration'];
	// Query placecode table to get the last placecode_id
	// Add one to the last id to create the new placecode name
	$update_placecode = "UPDATE placecodes SET status = '$placecode_status', placecode_description = '$placecode_desc', placecode_code = '$placecode_code', schedule='$schedule', registration = '$registration' WHERE placecode_id = '$placeid'";

	mysql_query($update_placecode, $studioAdmin) or die(mysql_error());
	
	// *********** ADD process to update any pages with this placecode
	//
	// Query placecode_page table for this placecode
	$query_placecode_page = "SELECT * FROM placecode_page WHERE placecode = '$placecode'";
	$get_placecode_page = mysql_query($query_placecode_page, $studioAdmin);
	$num_placecode_page = mysql_num_rows($get_placecode_page);
	// Loop through the results (if any)
	if($num_placecode_page >0) {
		while($row_placecode_page = mysql_fetch_assoc($get_placecode_page)) {
			// Pass page_id to createHTML function
			$page_id = $row_placecode_page['page_id'];
			createHTMLfile($page_id, $studioAdmin,$database_studioAdmin,$studioAdmin,$database_studioAdmin);
		}
	}
	
	header("Location: ezed_placecode_management.php");

}

// GET PLACECODES
//
$query_placecodes = "SELECT * FROM placecodes";
$get_placecodes = mysql_query($query_placecodes, $studioAdmin);
$num_placecodes = mysql_num_rows($get_placecodes);


// ****************************************************************
// UNIQUE to placecode management page
//*****************************************************************************
// ADD NEW PLACECODE
//
if(isset($_POST['placecode_desc']) && $_POST['placecode_desc'] <>'' && $_POST['placecode_code'] <>'') {
	$placecode_desc = $_POST['placecode_desc'];
	$placecode_status = $_POST['placecode_status'];
	$placecode_code = htmlentities($_POST['placecode_code'], ENT_QUOTES, "UTF-8");
	$schedule = $_POST['schedule'];
	$registration = $_POST['registration'];


	// Query placecode table to get the last placecode_id
	// Add one to the last id to create the new placecode name
	$query_last_placecode = "SELECT * FROM placecodes ORDER BY placecode_id DESC LIMIT 1";
	$get_last_placecode = mysql_query($query_last_placecode, $studioAdmin) or die(mysql_error());
	if(mysql_num_rows($get_last_placecode) >=1) {
		$row_last_placecode = mysql_fetch_assoc($get_last_placecode);
		$last_id = $row_last_placecode['placecode_id'];
		$next_id = $last_id + '1';
	} else {
		$next_id = 1;
	}
	$newplacecode = "[[placecode" . $next_id . "]]";

	
	$insert_query = "INSERT INTO placecodes (placecode_description,placecode,placecode_code,schedule,registration,status) VALUES ('$placecode_desc','$newplacecode','$placecode_code','$schedule','$registration','$placecode_status')";
	mysql_query($insert_query,$studioAdmin) or die(mysql_error());
	
	// Reload this page to clear form values
	header("Location: ezed_placecode_management.php");
}

// GET PLACECODES
//
$query_placecodes = "SELECT * FROM placecodes";
$get_placecodes = mysql_query($query_placecodes, $studioAdmin);
$num_placecodes = mysql_num_rows($get_placecodes);

//******************************************************************

// ** Logout the current user. **
$logoutAction = $_SERVER['PHP_SELF']."?doLogout=true";
if ((isset($_SERVER['QUERY_STRING'])) && ($_SERVER['QUERY_STRING'] != "")){
  $logoutAction .="&". htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	// Record logout
	$user = urlencode($_SESSION['MM_Username']);
	$site = urlencode($_SESSION['site_name']);
	$date = urlencode(date("n/j/Y g:i:s a"));
	$url = $_SESSION['URL'];
	$action = "Log out - From Page editor";
	$action = urlencode($action);
	get_url($user,$site,$date,$action,$url);
	
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

// **********************************************************
// Get page name from pages table using page_id passed in URL
// **********************************************************
mysql_select_db($database_studioAdmin, $studioAdmin);
$page_id = GetSQLValueString($_GET['page_id'], "int");
$query_getPages = "SELECT * FROM pages WHERE page_id = $page_id";
$getPages = mysql_query($query_getPages, $studioAdmin) or die(db_error_handle());
$row_getPages = mysql_fetch_assoc($getPages);
$page_name = $row_getPages['name'];
$file_name = $row_getPages['file'];
$page_title = $row_getPages['title'];
$page_keywords = $row_getPages['keywords'];
$page_description = $row_getPages['description'];
$page_extra_info = $row_getPages['extra_info'];
$page_header_img = $row_getPages['header_image'];
$page_header_file = $row_getPages['header_file'];
$page_footer_file = $row_getPages['footer_file'];
$_SESSION['page_name'] = $page_name;
$_SESSION['file_name'] = $file_name;
$_SESSION['page_title'] = $page_title;
$_SESSION['page_keywords'] = $page_keywords;
$_SESSION['page_description'] = $page_description;
$_SESSION['extra_info'] = $page_extra_info;
$_SESSION['header_image'] = $page_header_img;
$_SESSION['header_file'] = $page_header_file;
$_SESSION['footer_file'] = $page_footer_file;

$_SESSION['ReturnTo'] = "ezed_content.php?page_id=" . $page_id;

// **********************************************************
// Get site information from SITE table
// **********************************************************
mysql_select_db($database_studioAdmin, $studioAdmin);
$query_getSiteInfo = "SELECT * FROM site";
$getSiteInfo = mysql_query($query_getSiteInfo, $studioAdmin) or die(db_error_handle());
$row_getSiteInfo = mysql_fetch_assoc($getSiteInfo);

// Set site info to variables	
$site_name = $row_getSiteInfo['studio_name'];
$css_path = $row_getSiteInfo['css_path'];
$site_head = $row_getSiteInfo['site_head'];

// Set form action to call itself
$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

// **********************************************************
// If cancel button is clicked - exit to admin page
// **********************************************************
if((isset($_POST['Cancel'])) && ($_POST['Cancel'] == "Return to administration menu")) {
	mysql_close($studioAdmin);
	header('Location: ezed_admin.php');
	exit;
}

// ************************************************************
// If no buttons have been clicked - same as initial page load
// ************************************************************
$colname_getContent = "-1";
if (isset($_GET['page_id'])) {
  $colname_getContent = $_GET['page_id'];
}
// **********************************************************
// Check if there is a T record
// **********************************************************
mysql_select_db($database_studioAdmin, $studioAdmin);
$query_getTContent = sprintf("SELECT * FROM content WHERE status = 'T' AND page_id = %s", GetSQLValueString($colname_getContent, "int"));
$getContent = mysql_query($query_getTContent, $studioAdmin) or die(db_error_handle());
$totalRows_getTContent = mysql_num_rows($getContent);


if($totalRows_getTContent == 1) {
	//*******************************************************
	// Save T record into content variable
	// *******************************************************
	$row_getContent = mysql_fetch_assoc($getContent);
} elseif($totalRows_getTContent == 0) {
	// ************************************************************
	// Select content record where status = A and page_id = GET[page_id]
	// ************************************************************
	$query_getContent = sprintf("SELECT * FROM content WHERE status = 'A' AND page_id = %s", GetSQLValueString($colname_getContent, "int"));
	$getContent = mysql_query($query_getContent, $studioAdmin) or die(db_error_handle());
	$row_getContent = mysql_fetch_assoc($getContent);
	$totalRows_getContent = mysql_num_rows($getContent);
}
// Prepare page content for display into editor window
$editcontent = stripslashes($row_getContent['contents']);
$editcontent = htmlspecialchars($editcontent);

//*********************************************************************
//  GET ALL THE PAGES TO POPULATE THE SIDEBAR MENU
//********************************************************************
$query_getSidePages = "SELECT * FROM pages WHERE editable = 'Y' ORDER BY page_id";
$getSidePages = mysql_query($query_getSidePages, $studioAdmin) or die(db_error_handle());
//$row_getPages = mysql_fetch_assoc($getPages);

// ********************************************************************
// Query gallery_info table to get all galleries for this site
//
$query_gallery = "SELECT * FROM gallery_info";
$get_galleries = mysql_query($query_gallery, $studioAdmin) or die(db_error_handle());
$num_galleries = mysql_num_rows($get_galleries);

// Record action
$user = urlencode($_SESSION['MM_Username']);
$site = urlencode($_SESSION['site_name']);
$url = $_SESSION['URL'];
$date = urlencode(date("n/j/Y g:i:s a"));
$action = "Editing page: " . $page_id . " (" . $page_name . ")";
$action = urlencode($action);
get_url($user,$site,$date,$action, $url);

?><!DOCTYPE html>
<head>
<title><?php echo $_SESSION['site_name']; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" src="ckeditor_4_3/ckeditor.js"></script>
<script type="text/javascript" src="ckeditor_4_3/adapters/jquery.js"></script>

<link href="css/edit1.css" rel="stylesheet" type="text/css">

<?php include_once("ezed_modal_code.php"); ?>
</head>
<body bgcolor="443B34">
<div align="center">
<table width="1170" border="1" cellpadding="0" cellspacing="0" bordercolor="#000000" bgcolor="#FFFFFF">
  <tr> 
    <td valign="top" bordercolor="#FFFFFF"><div align="center"> 
        <table width="100%" border="0" cellpadding="0" cellspacing="0" bordercolor="#999999">
          <tr bgcolor="#FFFFFF">
            <td colspan="3"><table border="0" cellpadding="8" cellspacing="0" width="1000">
                  
                <tr> 
                  <td height="50px"><p align="left" class="text"><span class="subhead"><strong>Currently editing: <?php echo $page_name; ?></strong></span> <br>
Be sure to check out the help videos below!
                  </p></td>
                  <td>&nbsp;</td>
                </tr>
            </table></td> 
          </tr>
          <tr>
            <td width="12%" align="center" valign="top">
			  <table width="100%" cellspacing="0" cellpadding="0" border="0">
			   <tr><td class="sidenavmain" bgcolor="#DFF1FF" height="40px" ><div class="sidenavtext" style="margin-left:10px;border-bottom:thin;"><a onClick="checkSaved('ezed_admin.php')" href="#">Main Menu </a></div></td></tr>
			 <tr><td class="sidenav" height="40px"><div class="sidenavtext" style="margin-left:5px; overflow:hidden; width:170px;"><form name="pages" id="pages"><select style="width:190px;" class="sidenavtext" name=page_id onChange="pageChange(this);">
  	<option value="">Select a page to edit</option>
    <?php
				  	while($row_getPages = mysql_fetch_assoc($getSidePages))
					{
						echo "<option value=" . $row_getPages['page_id'] . ">" . $row_getPages['name'] . "</option>";
					}
					 mysql_data_seek($getPages,0);
					?>
  </select></form></div></td></tr>
			<tr><td class="sidenav" height="40px"><div class="sidenavtext" align="left" style="margin-left:10px;border-bottom:thin;"><a onClick="checkSaved('ezed_file_management.php')" >Manage Files </a></div></td>
			 </tr>
			 <tr><td class="sidenav" height="40px" ><div class="sidenavtext" align="left" style="margin-left:10px;border-bottom:thin;"><a onClick="checkSaved('ezed_image_management.php')">Manage Images</a></div></td></tr>
			 <?php 
					if($totalSchedules != 0) { ?> <tr><td class="sidenav" height="40px" ><div class="sidenavtext" align="left" style="margin-left:10px;border-bottom:thin;"><a onClick="checkSaved('ezed_schedule_management.php')">Manage Schedules</a></div></td></tr><?php } ?>
			 <tr><td class="sidenav" height="40px" ><div align="left" style="margin-left:10px;border-bottom:thin;"><span class="sidenavtext"><a onClick="checkSaved('ezed_placecode_management.php')">Manage Placecodes</a></span><span class="text"> <a style="text-decoration:none;" href="#" title="Go here to add code from external sources (Javascript, iframes, etc) into a page">(?)</a></span></div></td></tr>
			 <?php if($num_galleries >0) { ?><tr><td class='sidenav' height='40px' ><div  align='left' style='margin-left:10px;border-bottom:thin;'><span class='sidenavtext'>Manage Photo Galleries</span><br><?php while($gallery = mysql_fetch_assoc($get_galleries)) { ?>&nbsp;&nbsp;&nbsp;&nbsp;<a onClick="checkSaved('ezed_gallery_management.php?gallery=<?php echo $gallery['gallery_type']; ?>')"><span class="text"><?php echo $gallery['gallery_name']; ?></span></a><br> <?php } ?></div></td></tr> <?php } ?>
			 <tr><td class="sidenav" height="40px" ><div  align="left" style="margin-left:10px;border-bottom:thin;"><span class="sidenavtext"><a onClick="checkSaved('ezed_page_tag_editor.php')">Page meta tag editor</a></span><span class="text"> <a style="text-decoration:none;" href="#" title="Use this to update the page Title, Keyword and Description tags">(?)</a></span></div></td></tr>
			 <tr><td class="sidenav" height="40px" ><div  align="left" style="margin-left:10px;border-bottom:thin;"><span class="sidenavtext"><a onClick="checkSaved('ezed_script_management.php')">Page script editor</a></span><span class="text"> <a style="text-decoration:none;" href="#" title="Use this to add or update scripts in the header or footer of a page">(?)</a></span></div></td></tr>
			 <tr><td class="sidenav" height="40px"><div  class="sidenavtext" align="left"  style="margin-left:10px;border-bottom:thin;"><a onClick="checkSaved('ezed_backup.php?page_id=<?php echo $page_id; ?>')" href="#">View page archive</a>s</div></td>
			 </tr>
			 <tr><td class="sidenav" height="40px"><div class="sidenavtext" align="left" style="margin-left:10px;border-bottom:thin;"><a onClick="checkSaved('<?php echo $logoutAction ?>')" href="#">Logout</a></div></td></tr>
			<tr><td height="40px" bgcolor="#DFF1FF" class="sidenav"><div align="left" style="margin-left:10px;border-bottom:thin;">
			 <?php include("http://www.studioofdance.com/s2e_support/content_page_help.php"); ?>
			   </div></td>
			 </tr>
		    </table></td> 
            <td width="0%" valign="top" bgcolor="#DFF1FF">&nbsp;</td>
            <td width="88%" valign="top">