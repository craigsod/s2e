<?php

require_once('../../Connections/studioAdmin_i.php');
include_once('ezed_create_HTML_function.php');
include_once("ezed_schedule_function.php");
mysqli_select_db($studioAdmin, $database_studioAdmin);

//initialize the session
if (!isset($_SESSION)) {
  session_start();
}

function db_error_handle()
{
	mail('admin@studioofdancehosting.com', 'database connection error', 'An error occured in the preview file');
	header("Location: db_error.htm");
}

// This script is called from the schedule editing page when the update button is clicked and the contents have been saved

$schedule = htmlspecialchars($_GET['schedule_no']);

// Query placecodes for status = active, placecode = schedule number and registration or schedule are Y
$query_placecodes = "SELECT * FROM placecodes WHERE status = 'Active' && placecode_code = '$schedule' && (schedule = 'Y' || registration = 'Y')";
$getplacecodes = mysqli_query($studioAdmin, $query_placecodes);
$totalplacecodes = mysqli_num_rows($getplacecodes);
if($totalplacecodes <>0) {
	$row_placecode = mysqli_fetch_assoc($getplacecodes);
	$placecode = $row_placecode['placecode'];

	// Query placecode_page table for placecode
	$query_placecode_page = "SELECT * FROM placecode_page WHERE placecode = '$placecode'";
	$getplacecode_pages = mysqli_query($studioAdmin, $query_placecode_page);
	while($row_placecode_page = mysqli_fetch_assoc($getplacecode_pages)) {
		// For each page that the placecode (schedule) is on, create the HTML file
		$page_id = $row_placecode_page['page_id'];
		createHTMLfile($page_id, $studioAdmin,$database_studioAdmin,$studioAdmin,$database_studioAdmin);
	}

}
$returnto = $_SESSION['ReturnTo'];
header("Location: " . $returnto);
//header(sprintf("Location: %s", $updateGoTo));

?>
