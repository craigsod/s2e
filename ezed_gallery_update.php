<?php require_once('../../Connections/studioAdmin_i.php'); 

function log_error($error_sub,$error_msg)
{
	mail('admin@studioofdancehosting.com', $error_sub,$error_msg);
}



//initialize the session

if (!isset($_SESSION)) {
  session_start();
}





// Get domain name from session var and strip any leading characters

// Use this information for logging of user actions

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



// *************************************************************************

// GET GALLERY ID FROM URL

//

if(isset($_GET['gallery_id']) && $_GET['gallery_id'] <> '') {

	$gallerydir = $_GET['gallery_id'];

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

$action = "Updating gallery: " . $gallerydir;

$action = urlencode($action);

get_url($user,$site,$date,$action,$url);



// Read gallery folder and get list of image files

// Need error handling for non-image files or an empty folder

$dir = "../usercontent/images/" . $gallerydir;



// Create JSON gallery file from image file list

$galleryfile = "../" . $gallerydir . ".json";

$handle = fopen($galleryfile, "w");

$firstpart = "data = [";

$write = fwrite($handle, $firstpart);

// Open a known directory, and proceed to read its contents

$galleryfiles = array();
if (is_dir($dir)) {

    if ($dh = opendir($dir)) {

        while (($file = readdir($dh)) !== false) {

			if($file != "." && $file != "..") {

				$ext = strstr($file, '.');

				if($ext == ".jpg" || $ext == ".jpeg" || $ext == ".JPG" || $ext == ".GIF" || $ext == ".gif" || $ext == ".png" || $ext == ".PNG"){

					// Write file names to array
					
					$galleryfiles[] = '{ image: "usercontent/images/' . $gallerydir . "/" . $file . '" },';
				}

			}

        }

        closedir($dh);

    }

}
sort($galleryfiles, SORT_NATURAL | SORT_FLAG_CASE);
foreach($galleryfiles as $entry) {
  $write = fwrite($handle, $entry);
}

$lastpart = "];";

$write = fwrite($handle, $lastpart);

fclose($handle);



// Return to gallery management page with message

// Success or failure

$_SESSION['galleryupdated'] = "Y";

header("Location: ezed_gallery_management.php?gallery=$gallerydir");





?>