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
	//Before logging out - update userlog file
  	$logFile = "userlog.txt";
	$fh = fopen($logFile, 'a') or die(log_error("Log file creation error","Was unable to create or write to log file"));
	$str = $_SESSION['MM_Username'] . "," . date('n/j/y h:i:s A') . ",Logout from <page name here> " . "\n";
	fwrite($fh, $str);
	fclose($fh);
	
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
	mail('admin@studioofdancehosting.com', 'database connection error', 'An error occured in the ezed_script_edit.php file');
	header("Location: db_error.htm");
}

// Establish database connections
mysqli_select_db($studioAdmin, $database_studioAdmin);


$script_id = $_GET['id'];
$page_id = $_GET['page'];

if($page_id =='') {
	header("Location: ezed_admin.php");
	exit();
}

$query_script = "SELECT * FROM scripts WHERE script_id = '$script_id'";
$get_script = mysqli_query($studioAdmin, $query_script);
$row_getscript = mysqli_fetch_assoc($get_script);
$script = $row_getscript['script'];

// Save script changes
if(isset($_POST['submit']) && $_POST['submit'] <> "" && isset($_POST['scriptid'])) {

	$scriptid = $_POST['scriptid'];
	$script_status = $_POST['script_status'];
	$script_desc = $_POST['script_desc'];
	$script_code = htmlentities($_POST['script_code'], ENT_QUOTES, "UTF-8");
	$location = $_POST['location'];
	// Query script table to get the last script_id
	// Add one to the last id to create the new script name
	$update_script = "UPDATE scripts SET status = '$script_status', script_description = '$script_desc', script_code = '$script_code', location = '$location' WHERE script_id = '$scriptid'";

	mysqli_query($studioAdmin, $update_script);
	
	// *********** Update this page with this script
	//
	
	createHTMLfile($page_id, $studioAdmin,$database_studioAdmin,$studioAdmin,$database_studioAdmin);
	
	header("Location: ezed_script_management.php?page_id=" . $page_id);

}

// **********************************************************
// Get site information from SITE table
// **********************************************************

$query_getSiteInfo = "SELECT * FROM site";
$getSiteInfo = mysqli_query($studioAdmin,$query_getSiteInfo);
$row_getSiteInfo = mysqli_fetch_assoc($getSiteInfo);

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


// Set form action to call itself
$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

//*********************************************************************
//  GET ALL THE PAGES TO POPULATE THE SIDEBAR MENU
//********************************************************************
$query_getSidePages = "SELECT * FROM pages WHERE editable = 'Y' ORDER BY page_id";
$getSidePages = mysqli_query($studioAdmin, $query_getSidePages);
//$row_getPages = mysqli_fetch_assoc($getPages);

//*********************************************************************
// GET scriptS
//
$query_scripts = "SELECT * FROM scripts WHERE status = 'Active'";
$get_scripts = mysqli_query($studioAdmin, $query_scripts);
$num_scripts = mysqli_num_rows($get_scripts);


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
                  <td height="50px"><div align="left"><span class="subhead">Simple2Edit Administration Menu for</span> <span class="style15"><strong><?php echo $_SESSION['site_name']; ?></strong></span>
                      <?php if($_SESSION['MM_UserGroup'] == 3) {echo "<br><span class='warning'>You are logged in as an administrator</span>";} ?>
                  </div></td>
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
			    <tr><td valign="top">
			    <p align="left" class="buttons"><strong>Script editing</strong></p>
				<form method="post" action="">
				  <p align="left"><span class="text"><strong>Description:</strong><br>
				    </span>
				    <input name="script_desc" type="text" value="<?php echo $row_getscript['script_description']; ?>" size="30" >
				  </p>
				  <p align="left"><span class="text"><strong>Script status: 
				    </strong></span>
				    <input name="script_status" type="radio" value="Active" <?php if($row_getscript['status'] == 'Active') { echo "checked";} ?>>
				    <span class="text">				    Active				    </span>
				    <input name="script_status" type="radio" value="Inactive" <?php if($row_getscript['status'] == 'Inactive') { echo "checked";} ?>>
				    <span class="text">Inactive</span></p>
				  <p align="left">
				      <span class="text"><strong>Code:</strong></span><br> 
				      <textarea name="script_code" cols="80" rows="5" id="script_code"><?php echo htmlspecialchars_decode($row_getscript['script_code']); ?></textarea>
				    </p>
				  <p align="left"><span class="text"><strong>Type</strong></span><br>
			            <span class="text">Location: 
			            <input name="location" type="radio" value="Header" <?php if($row_getscript['location'] == 'Header') { echo "checked";} ?>>
				  Header 
				  <input name="location" type="radio" value="Footer" <?php if($row_getscript['location'] == 'Footer') { echo "checked";} ?>>
				  Footer
				  <br>
		              </span> </p>
				  <p align="left">
				  <input type="hidden" name="scriptid" value="<?php echo $row_getscript['script_id']; ?>">
				    <input type="submit" name="submit" value="Save changes">
				      </p>
				  <p align="left" class="text"><a href="ezed_script_management.php?page_id=<?php echo $page_id; ?>">Return to Script management page</a> </p>
				  <p>
				</form>
				</p>
				<p class="text">&nbsp;</p></td>
			    </tr>
				</table></td>
          </tr>
        </table>
          
        </div>    </td>
  </tr>
</table>
</body>
</html>