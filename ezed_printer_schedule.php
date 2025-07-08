<?php
require_once('../../Connections/studioAdmin_i.php');
include_once("schedule_builders.php");

//initialize the session
if (!isset($_SESSION)) {
  session_start();
}

function db_error_handle()
{
	mail('admin@studioofdancehosting.com', 'database connection error', 'An error occured in the create printer friendly schedule file');
	header("Location: db_error.htm");
}

// This script is called from the schedule page of the website. It creates
// a printer friendly version of the schedule.

$schedule = 1;
					
// Query the sched_info table to get table properties
mysqli_select_db($studioAdmin, $database_studioAdmin);
$query_getSchedInfo = "SELECT * FROM sched_info WHERE schedule_no='$schedule'";
echo $query_getSchedInfo;
$getSchedInfo = mysqli_query($studioAdmin, $query_getSchedInfo);
$row_getSchedInfo = mysqli_fetch_assoc($getSchedInfo);
$totalSchedules = mysqli_num_rows($getSchedInfo);
$type = $row_getSchedInfo['sched_type'];
echo "the total is: " . $totalSchedules;
exit();
$studio = $row_getSchedInfo['studio'];
$table_width = $row_getSchedInfo['max_width'];
$section_bg_color = $row_getSchedInfo['section_bg_color'];
$section_text_color = $row_getSchedInfo['section_text_color'];
$studio_text_color = $row_getSchedInfo['studio_text_color'];


mysqli_data_seek($getSchedInfo,0);
					
if($type == "Across") {
	$test = schedule_across($schedule);
} elseif($type == "Across BY studio") {
	$test = schedule_across_studio($schedule);
} elseif($type == "Down") {
	$test = schedule_down($schedule);
} elseif($type == "Down BY studio") {
	$test = schedule_down_studio($schedule);
} elseif($type == "Class") {
	$test = schedule_classes($schedule);
} elseif($type == "Down BY studio2") {
		$test = schedule_down_studio_across($schedule);
} else {
	echo "that type of schedule does not exist";
}

foreach($test as $t) {$matrix .= $t;}

?>