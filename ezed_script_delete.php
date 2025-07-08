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


// Establish database connections

mysqli_select_db($studioAdmin, $database_studioAdmin);

$script_id = $_GET['id'];
$page_id = $_GET['page'];


$delete_script = "DELETE FROM scripts WHERE script_id = '$script_id'";
mysqli_query($studioAdmin, $delete_script);

createHTMLfile($page_id, $studioAdmin,$database_studioAdmin,$studioAdmin,$database_studioAdmin);

header("Location: ezed_script_management.php?page_id=" . $page_id);
?>