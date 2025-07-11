<?php
require_once('../../Connections/studioAdmin_i.php');

//initialize the session
if (!isset($_SESSION)) {
  session_start();
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

mysqli_select_db($studioAdmin, $database_studioAdmin);
$query_getPages = "SELECT * FROM pages";
$getPages = mysqli_query($studioAdmin, $query_getPages) or die(db_error_handle());
$num_pages = mysqli_num_rows($getPages);
?>
Use this form to pre-process an existing installation of Simple2Edit to the new version.

<form action="ezed_upgrade.php" method="post">
  
	<?php
while($row_getPages = mysqli_fetch_assoc($getPages))
	{ ?>
		<p><?php echo $row_getPages['name']; ?>&nbsp;&nbsp;<input type="checkbox" name="page<?php echo $row_getPages['page_id']; ?>" ></p>
	    <p>
	      <?php }
 mysqli_data_seek($getPages,0); 
?>
  </p>
	    <p>CSS file name: 
          <input type="text" id="cssfile" name="cssfile" />
          <br />
          <input type="submit" name="submit" value="submit">
                </p>
</form>
<p><a href="ezed_admin.php">Return to admin menu</a> </p>
