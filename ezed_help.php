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


// Get domain name from session var and strip any leading characters
$domain = $_SESSION['URL'];
$www_position = strpos($domain, '.');
$stripped_domain = substr($domain,$www_position+1);


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


// **********************************************************
// Get site information from SITE table
// **********************************************************
mysqli_select_db($studioAdmin, $database_studioAdmin);
$query_getSiteInfo = "SELECT * FROM site";
$getSiteInfo = mysqli_query($studioAdmin, $query_getSiteInfo);
$row_getSiteInfo = mysqli_fetch_assoc($getSiteInfo);


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
$action = "Accessing image management";
$action = urlencode($action);
get_url($user,$site,$date,$action,$url);

// ********************************************************************
// Query gallery_info table to get all galleries for this site
//
$query_gallery = "SELECT * FROM gallery_info";
$get_galleries = mysqli_query($studioAdmin, $query_gallery);
$num_galleries = mysqli_num_rows($get_galleries);

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
            <td colspan="3"><table border="0" cellpadding="8" cellspacing="0" width="1000">
                  
                <tr> 
                  <td height="50px"><p align="left" class="subhead">Image Management</p></td>
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
            <td width="88%" valign="top">
			<div style="background-color:#DFF1FF; height:5px;"></div>
<?php 
			  	$topic = $_GET['topic'];

				switch($topic) {
					case "help":
						include("http://www.studioofdance.com/s2e_support/mainhelp.php");
						break;
					case "tablephoto":
						include("http://www.studioofdance.com/s2e_support/table-with-photo.php");
						break;
				}
			  	?></td>
          </tr>
        </table>
          
        </div>    </td>
  </tr>
</table>
</body>
</html>