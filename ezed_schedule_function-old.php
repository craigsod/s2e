<?php 
function createSchedule ($page_id, $studioAdmin,$database_studioAdmin, $schedule) {
	// Include schedule builder function here
	include_once("schedule_builders.php");
	
	// Check to see if there are any records in the schedule
	$query_scheduleRecords = "SELECT * FROM schedule WHERE schedule_no = '$schedule'";
	$getScheduleRecords = mysqli_query($studioAdmin, $query_scheduleRecords);
	$rows_getScheduleRecords = mysqli_num_rows($getScheduleRecords);

	//If no records - exit process
	if($rows_getScheduleRecords <> 0) {
	
	
		// Query the sched_info table to get table properties
		$query_getSchedInfo = "SELECT * FROM sched_info WHERE schedule_no='$schedule'";
		$getSchedInfo = mysqli_query($studioAdmin, $query_getSchedInfo);
		$row_getSchedInfo = mysqli_fetch_assoc($getSchedInfo);
		$type = $row_getSchedInfo['sched_type'];
		$studio = $row_getSchedInfo['studio'];
		$table_width = $row_getSchedInfo['max_width'];
		$section_bg_color = $row_getSchedInfo['section_bg_color'];
		$section_text_color = $row_getSchedInfo['section_text_color'];
		$studio_text_color = $row_getSchedInfo['studio_text_color'];
		
		mysqli_data_seek($getSchedInfo,0);
			
		if($type == "Across") {
			$test = schedule_across($schedule,$studioAdmin, $getSchedInfo, $row_getSchedInfo);
		} elseif($type == "Across BY studio") {
			$test = schedule_across_studio($schedule,$studioAdmin, $getSchedInfo, $row_getSchedInfo);
		} elseif($type == "Down") {
			$test = schedule_down($schedule,$studioAdmin, $getSchedInfo, $row_getSchedInfo);
		} elseif($type == "Down BY studio") {
			$test = schedule_down_studio($schedule,$studioAdmin, $getSchedInfo, $row_getSchedInfo);
		} elseif($type == "Class") {
			$test = schedule_classes($schedule,$studioAdmin, $getSchedInfo, $row_getSchedInfo);
		} elseif($type == "Down BY studio2") {
				$test = schedule_down_studio_across($schedule,$studioAdmin, $getSchedInfo, $row_getSchedInfo);
		} else {
			echo "that type of schedule does not exist";
		}
		// Build schedule table
		foreach($test as $t) {$matrix .= $t;}
	}
return $matrix;
}
?>