<?php require_once('../Connections/studioAdmin.php'); ?>
<?php
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

$MM_restrictGoTo = "../login_v1.php";
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
?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "updateuser")) {
	// check for valid email address
	$pattern = '/^[^@]+@[^\s\r\n\'";,@%]+$/';
	if (!preg_match($pattern, trim($_POST['email']))) {
 	 	$error = 'Please enter a valid email address';
  	} else {
  		$updateSQL = sprintf("UPDATE users SET username=%s, pwd=%s, email=%s, status=%s WHERE user_id=%s",
                       GetSQLValueString($_POST['username'], "text"),
                       GetSQLValueString($_POST['pwd'], "text"),
                       GetSQLValueString($_POST['email'], "text"),
                       GetSQLValueString($_POST['status'], "text"),
                       GetSQLValueString($_POST['user_id'], "int"));

  		mysqli_select_db($studioAdmin, $database_studioAdmin);
 		 $Result1 = mysqli_query($studioAdmin, $updateSQL);

  		$updateGoTo = "admin_v1.php";
 		 if (isset($_SERVER['QUERY_STRING'])) {
  		  $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
   		 $updateGoTo .= $_SERVER['QUERY_STRING'];
 		 }
 		 header(sprintf("Location: %s", $updateGoTo));
	}
}
	
	
mysqli_select_db($studioAdmin, $database_studioAdmin);
$query_updateNewUser = "SELECT * FROM users WHERE pwd = '123456' AND status = 'New'";
$updateNewUser = mysqli_query($studioAdmin, $query_updateNewUser);
$row_updateNewUser = mysqli_fetch_assoc($updateNewUser);
$totalRows_updateNewUser = mysqli_num_rows($updateNewUser);
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Figtree Dance Studio -Maintenance Login</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<link href="../7.css" rel="stylesheet" type="text/css">
<link href="../admin.css" rel="stylesheet" type="text/css">
</head>

<body bgcolor="443B34" link="#FFFFFF" vlink="#FFFFFF" alink="#FFFFFF" onLoad="MM_preloadImages('../images/images/nav_r2_c9_f3.gif','../images/images/nav_r2_c9_f2.gif','../images/images/nav_r2_c9_f4.gif','../images/images/nav_r6_c6_f3.gif','../images/images/nav_r6_c6_f2.gif','../images/images/nav_r6_c6_f4.gif')">
<div align="center">
  <table width="780" border="2" cellpadding="0" cellspacing="0" bordercolor="#000000" bgcolor="#FFFFFF">
    <tr> 
      <td valign="top" bordercolor="#FFFFFF"><div align="center"> 
          <table width="100%" border="0" cellspacing="0">
            <tr> 
              <td bgcolor="060606"><table border="0" cellpadding="0" cellspacing="0" width="776">
                  <!-- fwtable fwsrc="1subnav.png" fwbase="nav.gif" fwstyle="Dreamweaver" fwdocid = "742308039" fwnested="0" -->
                  <tr> 
                    <td><img src="../images/images/spacer.gif" alt="" name="undefined_2" width="23" height="1" border="0"></td>
                    <td><img src="../images/images/spacer.gif" alt="" name="undefined_2" width="115" height="1" border="0"></td>
                    <td><img src="../images/images/spacer.gif" alt="" name="undefined_2" width="13" height="1" border="0"></td>
                    <td><img src="../images/images/spacer.gif" alt="" name="undefined_2" width="114" height="1" border="0"></td>
                    <td><img src="../images/images/spacer.gif" alt="" name="undefined_2" width="13" height="1" border="0"></td>
                    <td><img src="../images/images/spacer.gif" alt="" name="undefined_2" width="119" height="1" border="0"></td>
                    <td><img src="../images/images/spacer.gif" alt="" name="undefined_2" width="2" height="1" border="0"></td>
                    <td><img src="../images/images/spacer.gif" alt="" name="undefined_2" width="58" height="1" border="0"></td>
                    <td><img src="../images/images/spacer.gif" alt="" name="undefined_2" width="84" height="1" border="0"></td>
                    <td><img src="../images/images/spacer.gif" alt="" name="undefined_2" width="53" height="1" border="0"></td>
                    <td><img src="../images/images/spacer.gif" alt="" name="undefined_2" width="182" height="1" border="0"></td>
                    <td><img src="../images/images/spacer.gif" alt="" name="undefined_2" width="1" height="1" border="0"></td>
                  </tr>
                  <tr> 
                    <td colspan="11"><img name="nav_r1_c1" src="../images/images/nav_r1_c1.gif" width="776" height="52" border="0" alt=""></td>
                    <td><img src="../images/images/spacer.gif" alt="" name="undefined_2" width="1" height="52" border="0"></td>
                  </tr>
                  <tr> 
                    <td rowspan="2" colspan="8"><img name="nav_r2_c1" src="../images/images/nav_r2_c1.gif" width="457" height="60" border="0" alt=""></td>
                    <td colspan="2"><a href="/news.htm" target="_top" onMouseOut="MM_nbGroup('out');" onMouseOver="MM_nbGroup('over','nav_r2_c9','../images/images/nav_r2_c9_f2.gif','../images/images/nav_r2_c9_f4.gif',1)" onClick="MM_nbGroup('down','navbar1','nav_r2_c9','../images/images/nav_r2_c9_f3.gif',1)"></a></td>
                    <td rowspan="8">&nbsp;</td>
                    <td><img src="../images/images/spacer.gif" alt="" name="undefined_2" width="1" height="59" border="0"></td>
                  </tr>
                  <tr> 
                   
                    <td><img src="../images/images/spacer.gif" alt="" name="undefined_2" width="1" height="1" border="0"></td>
                  </tr>
                 
                  <tr> 
                    <td colspan="3">&nbsp;</td>
                    <td rowspan="3">&nbsp;</td>
                    <td colspan="3"><img name="nav_r5_c5" src="../images/images/nav_r5_c5.gif" width="134" height="17" border="0" alt="">
                 
                </table>
</td>
            </tr>
 
            <tr> 
              <td class="secondlink"><blockquote>

                <?php
	if($error) {
		echo "<p class='warning'>" . $error . "</p>";
	} else {
		echo "<p>&nbsp;</p>";
	}
?>
<form id="form1" name="updateuser" method="POST" action="<?php echo $editFormAction; ?>">
  <p class="text">New username
    <input type="text" name="username" />
  </p>
  <p class="text">
    New password
    <input type="text" name="pwd" />
  </p>
  <p class="text">
    confirm password
    <input type="text" name="confirmpwd" />
  </p>
  <p class="text">email address
    <input type="text" name="email" />
  </p>
  <p>
    <span class="text">
    <input type="submit" name="Submit" value="Submit" />
  </span></p>
  <p><a href="../login_v1.php">Return to login page </a></p>
  <input type="hidden" name="MM_update" value="updateuser">
  <input type="hidden" name="status" value="Active"  />
  <input type="hidden" name="user_id" value="<?php echo $row_updateNewUser['user_id']; ?>" />
</form>
<script language="javascript">
document.forms[0].onsubmit = function() {
if(this.elements['pwd'].value != this.elements['confirmpwd'].value) {
alert('Passwords do not match');
return false;
}

return true;
}
</script>
                <p>&nbsp;</p>
                  <div align="left"></div>
                </blockquote></td>
            </tr>
          </table>
          
        </div>
      </td>
    </tr>
  </table>
</div>
</body>
</html>
<?php
mysqli_free_result($updateNewUser);
?>