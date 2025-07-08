<?php
require_once('../../Connections/studioAdmin_i.php');

//initialize the session
if (!isset($_SESSION)) {
  session_start();
}

function db_error_handle()
{
	mail('admin@studioofdancehosting.com', 'database connection error', 'An error occured in the createHTML file');
	header("Location: db_error.htm");
}
$_SESSION['ReturnTo'] = "ezed_admin.php";

$page_id = $_SESSION['page_id'];

$schedule = $_GET['schedule_no'];
$updated = $_GET['updated'];

mysqli_select_db($studioAdmin, $database_studioAdmin);
$updateSQL = "UPDATE schedule SET status='B' WHERE status = 'A' AND schedule_no = '$schedule'";
$Result1 = mysqli_query($studioAdmin, $updateSQL) or die(db_error_handle());
	
$updateSQL = "UPDATE schedule SET status='A' WHERE schedule_no= '$schedule' AND updated='$updated'";
$Result1 = mysqli_query($studioAdmin, $updateSQL) or die(db_error_handle());


// Call create_HTML script to put the header and footer together with the contents and 
// create a static HTML page on the server
$createHTML = "ezed_create_HTML.php?page_id=$page_id";
header(sprintf("Location: %s", $createHTML));
?>