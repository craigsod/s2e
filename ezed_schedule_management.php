<?php require_once('../../Connections/studioAdmin_i.php'); ?>
<?php
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
	mail('admin@studioofdancehosting.com', 'database connection error', 'An error occured in the index.php file');
	header("Location: db_error.htm");
}


// **********************************************************
// Get site information from SITE table
// **********************************************************
mysqli_select_db($studioAdmin, $database_studioAdmin);
$query_getSiteInfo = "SELECT * FROM site";
$getSiteInfo = mysqli_query($studioAdmin, $query_getSiteInfo);
$row_getSiteInfo = mysqli_fetch_assoc($getSiteInfo);


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

// ********************************************************************
// CHECK FOR ANY SCHEDULES ON THIS SITE
//
$query_getSchedules = "SELECT * FROM page_schedule GROUP BY schedule_no";
$getSchedules = mysqli_query($studioAdmin, $query_getSchedules);
$totalSchedules = mysqli_num_rows($getSchedules);

// REMOVE THE CHECK ABOVE AND REPLACE WITH A CHECK OF PLACECODES
// Query placecodes table where status = active and type is registration or schedule
// Use this set to display the placecode for this schedule
$query_placecodes = "SELECT * FROM placecodes WHERE status = 'Active' && (schedule = 'Y' || registration = 'Y')";
$getplacecodes = mysqli_query($studioAdmin, $query_placecodes) or die(db_error_handle());
$totalplacecodes = mysqli_num_rows($getplacecodes);

// Get site information from site table to determine which menu items are available
$query_getSiteInfo = "SELECT * FROM site";
$getSiteInfo = mysqli_query($studioAdmin, $query_getSiteInfo) or die(db_error_handle());
$row_getSiteInfo = mysqli_fetch_assoc($getSiteInfo);

$placecodes = strtoupper($row_getSiteInfo['placecodes']);
$page_tag = strtoupper($row_getSiteInfo['pagetag']);
$page_script = strtoupper($row_getSiteInfo['pagescript']);
$my_account = strtoupper($row_getSiteInfo['myaccount']);

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
<style type="text/css">
<!--
.style1 {color: #FF0000}
-->
</style>
</head>

<body bgcolor="443B34">
<div align="center">
<table width="1170" border="1" cellpadding="0" cellspacing="0" bordercolor="#000000" bgcolor="#FFFFFF">
  <tr> 
    <td valign="top" bordercolor="#FFFFFF"><div align="center"> 
        <table width="100%" border="0" cellpadding="0" cellspacing="0" bordercolor="#999999">
          <tr bgcolor="#FFFFFF">
            <td colspan="4"><table border="0" cellpadding="8" cellspacing="0" width="1000">
                  
                <tr> 
                  <td width="233" height="50px"><p align="left" class="subhead">Schedule Management</p></td>
                  <td width="735" class="sidenavtext"><div align="left"><a href="http://<?php echo $_SESSION['URL']; ?>" target="_blank">View website in new window</a></div></td>
                </tr>
            </table></td> 
          </tr>
          <tr>
            <td width="200px" align="center" valign="top">
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
			 <?php if($placecodes == 'Y' || $_SESSION['MM_UserGroup'] == 3) {?><tr><td class="sidenav" height="40px" ><div align="left" style="margin-left:10px;border-bottom:thin;"><span class="sidenavtext"><a href="ezed_placecode_management.php">Manage Placecodes</a></span><span class="text"> <a style="text-decoration:none;" href="#" title="Go here to add code from external sources (Javascript, iframes, etc) into a page">(?)</a></span></div></td></tr><?php } ?>
			 <?php if($num_galleries >0) { echo "<tr><td class='sidenav' height='40px' ><div  align='left' style='margin-left:10px;border-bottom:thin;'><span class='sidenavtext'>Manage Photo Galleries</span><br>"; while($gallery = mysqli_fetch_assoc($get_galleries)) { echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href='ezed_gallery_management.php?gallery=" . $gallery['gallery_type'] . "'><span class='text'>" . $gallery['gallery_name'] . "</span></a><br>"; } echo "</div></td></tr>"; }?>
			 <?php if($page_tag == 'Y' || $_SESSION['MM_UserGroup'] == 3) {?><tr><td class="sidenav" height="40px" ><div  align="left" style="margin-left:10px;border-bottom:thin;"><span class="sidenavtext"><a href="ezed_page_tag_editor.php">Page tag editor</a></span><span class="text"> <a style="text-decoration:none;" href="#" title="Use this to update the page Title, Keyword and Description tags">(?)</a></span></div></td></tr><?php } ?>
			 <?php if($page_script == 'Y' || $_SESSION['MM_UserGroup'] == 3) {?><tr><td class="sidenav" height="40px" ><div  align="left" style="margin-left:10px;border-bottom:thin;"><span class="sidenavtext"><a href="ezed_script_management.php">Page script editor</a></span><span class="text"> <a style="text-decoration:none;" href="#" title="Use this to add or update scripts in the header or footer of a page">(?)</a></span></div></td></tr><?php } ?>
			 <tr><td class="sidenav" height="40px" ><div class="sidenavtext" align="left" style="margin-left:10px;border-bottom:thin;"><a href="ezed_my_account.php">My Account</a></div></td></tr>
			 <?php if($_SESSION['MM_UserGroup'] == 3) { ?><tr><td class="sidenav" height="40px" ><div class="sidenavtext" align="left" style="margin-left:10px;border-bottom:thin;"><a href="ezed_site_admin.php">Site Administration </a></div></td></tr> <?php } ?>
			 <tr><td class="sidenav" height="40px"><div class="sidenavtext" align="left" style="margin-left:10px;border-bottom:thin;"><a onClick="window.location='<?php echo $logoutAction ?>'" href="#">Logout</a></div></td></tr>
		    </table></td> 
            <td width="0%" valign="top" bgcolor="#DFF1FF">&nbsp;</td>
            <td width="88%" valign="top"><div style="background-color:#DFF1FF; height:5px;"></div>
			<table width="100%" border="0" cellpadding="8" cellspacing="0">
			  <tr>
			    <td width="54%" valign="top">
			      <p align="left" class="text"><strong>Choose a schedule below to edit</strong></p>			      <table width="499" border="0" align="left" cellpadding="8" cellspacing="0">
			        <tr><td width="175" class="text"><div align="left"><strong>Schedule name</strong></div></td>
			      <td width="165" class="text"><div align="left"><strong>Placement code*</strong></div></td>
			      <td width="111" class="text"><div align="left"><strong>&nbsp;</strong></div></td>
			      </tr>
			        <?php
				if($totalplacecodes >0) {
						while($row_placecodes = mysqli_fetch_assoc($getplacecodes))
						{
							echo '<tr><td><div align="left"><a href="ezed_schededit.php?schedule_no=' . $row_placecodes['placecode_code'] . '" class="link">' . $row_placecodes['placecode_description'] .'</a></div></td><td><div align="left">' . $row_placecodes['placecode'] . '</div></td><td><div align="left"><a href="ezed_schedule_backup.php?schedule_no=' . $row_placecodes['placecode_code'] . '" class="link">Backups</a></div></td></tr>';
						} 
				}?>
			        <tr>
			          <td colspan="3" class="text"><div align="left">* Copy the <em><strong>[[placecode]]</strong></em> above and paste it anywhere on the page where you want the schedule to appear. The placement code includes both left and right side brackets. </div></td>
				      </tr>
			        </table></td><td width="46%" valign="top" class="text"><p align="left"><?php
					if($_SESSION['MM_UserGroup'] == 3) { ?>
					    <span class="style1">This feature is only available to administrators</span><br>
					    <br>
					    <strong>Add new schedule</strong> (add this functionality later) <br>
					    <br>
						  
						  <form action="ezed_schedule_add.php" method="post" >
						    <div align="left">Schedule name: 
						      <input name="name" id="name" size="15">
						      <input type="submit" name="submit" value="Create table">
						    </div>
						  </form>
						  <?php } ?>
						  &nbsp;</p>
						  </td>
			</tr>
            <tr>
              <td colspan="2" class="text"><p>&nbsp;</p>
                <p>&nbsp;</p></td></tr>
			</table></td>
          </tr>
        </table>
          
        </div>    </td>
  </tr>
</table>
</div>
</body>
</html>