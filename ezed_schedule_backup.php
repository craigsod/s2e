<?php require_once('../../Connections/studioAdmin_i.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
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

$_SESSION['page_id'] = $_GET['page_id'];

$maxRows_getBackups = 20;
$recno = $_GET['schedule_no'];
mysqli_select_db($studioAdmin, $database_studioAdmin);
$queryCountSaves = "SELECT DISTINCT updated FROM schedule WHERE schedule_no='$recno' AND status='B' ORDER BY updated DESC";
$getCountSaves = mysqli_query($studioAdmin, $queryCountSaves);
$row_getCountSaves = mysqli_fetch_assoc($getCountSaves);
$totalRows_getCountSaves = mysqli_num_rows($getCountSaves);

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<style type="text/css">
<!--
.style1 {font-family: Arial, Helvetica, sans-serif}
.style3 {font-family: Arial, Helvetica, sans-serif; font-weight: bold; }
-->
</style>
<link href="css/edit1.css" rel="stylesheet" type="text/css">
<style type="text/css">
<!--
.style4 {
	font-size: 14;
	font-weight: bold;
}
-->
</style>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<title>Previously Saved Schedules</title>
</head>

<body bgcolor="443B34">
<div align="center">
  <table width="780" border="2" cellpadding="4" cellspacing="0" bordercolor="#999999" bgcolor="#FFFFFF">
      <tr> 
      <td width="100%" align="center" valign="top" bgcolor="#CCCCCC"><table width="100%" bgcolor="#CCCCCC">
        <tr>
          <td class="subhead" align="left">Restore schedule backups</td>
          <td align="right"><button class="buttons" title="Changes since your last save will be lost" onClick="location.href='ezed_admin.php'">Return to administration menu</button>&nbsp;&nbsp;&nbsp;<button class="buttons" title="Your changes will not be saved" onClick="window.location='<?php echo $logoutAction ?>'">Logout</button></td></tr></table></td>
    </tr>
    <tr> 
      <td colspan="2" valign="top" bordercolor="#FFFFFF"><div align="center"> 
          <table width="100%" border="0" align="left" cellspacing="0">
            <tr> 
              <td class="secondlink"><blockquote>
			  	<?php 
				if($totalRows_getCountSaves !=0){
				?> 
                <p align="right">&nbsp;</p>
                <p align="left" class="text"><strong>Previously saved versions of the schedule. </strong></p>
                <p align="left" class="text">Click on the RESTORE link to restore the schedule back to that date. </p>
                <p align="left" class="text">The most recent back-up is at top of the list. All times are Central Daylight Time </p>
                <p align="left" class="text"><a href="ezed_schedule_management.php">Return to Schedule Management page</a></p>
                <table width="500" border="1" align="left" cellpadding="6" cellspacing="0" bordercolor="#999999">
                  <tr>
                    <th width="264" scope="col"><span class="style3">Date schedule was backed up </span></th>
                    <th width="126" scope="col">&nbsp;</th>
                    <th width="126" scope="col">&nbsp;</th>
                  </tr>
                  <?php do { 
				  $updated = strtotime($row_getCountSaves['updated']); ?>
                    <tr>
                        <td><div align="center" class="text style4">
                          <div align="left"><?php echo date('m/j/Y - h:i:s A',$updated); ?></div>
                        </div></td>
                      <td><div align="center" class="text"><a href="ezed_schedule_backup_view.php?schedule_no=<?php echo $recno; ?>&updated=<?php echo $row_getCountSaves['updated']; ?>" target="_blank" class="link">VIEW</a></div></td>
                      <td><div align="center" class="style1"><a href="ezed_schedule_restore.php?schedule_no=<?php echo $recno; ?>&updated=<?php echo $row_getCountSaves['updated']; ?>" class="link">RESTORE</a></div></td>
                    </tr>
                    <?php } while ($row_getCountSaves = mysqli_fetch_assoc($getCountSaves)); ?>
                </table>
			<?php } else { ?> 
				<p>&nbsp;</p>
				<p align="left" class="text"><strong>There are no backup copies for the schedule yet.</strong><br>When you make a change to the schedule and save it, a backup will be created and it will show up here. </strong></p>
				    <p>
			<?php } ?>
                   <p>&nbsp;</p>
              </blockquote>
                </td>
            </tr>
          </table>
          
        </div>      </td>
    </tr>
  </table>
  <p>&nbsp;</p>
</div>

</body>
</html>
<?php
mysqli_free_result($getCountSaves);
?>