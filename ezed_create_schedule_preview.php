<?php

// *****************************************************************
// THIS PAGE CONTAINS A SCHEDULE
//******************************************************************

// PROCESS SCHEDULE PAGES WITH SCHEDULES
$query_getSchedules = "SELECT * FROM page_schedule WHERE page_id = '$page_id' AND reg_page='n'";
$getSchedules = mysqli_query($studioAdmin,$query_getSchedules) or die(db_error_handle());
$total_Schedules = mysqli_num_rows($getSchedules);
if($total_Schedules !=0) {
	$sched_content = $content;
	
	// Process the schedule on this page
	while($row_getSchedules = mysqli_fetch_assoc($getSchedules)) {
		$schedule = $row_getSchedules['schedule_no'];
		
		// Query the sched_info table to get table properties
		$query_getSchedInfo = "SELECT * FROM sched_info WHERE schedule_no='$schedule'";
		$getSchedInfo = mysqli_query($studioAdmin, $query_getSchedInfo) or die(db_error_handle());
		$row_getSchedInfo = mysqli_fetch_assoc($getSchedInfo);
		$type = $row_getSchedInfo['sched_type'];
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

		$str = '[[schedule' . $schedule . ']]';
		
		$contents_with_matrix = str_ireplace($str,$matrix,$sched_content);
		$matrix = "";
		$sched_content = $contents_with_matrix;
	}
	?>
	<div onclick="self.close();" style="background-color:#FFFFCC; border:thin; border-width:2px; border-style:solid; width:200px; padding:10px; margin-right:30px; margin-left:600px; top:30px; position:absolute;">Close this preview window</div>
	<?php
	include($header_file);
	echo stripslashes($sched_content);
	include($footer_file);
	exit;
}
//*************************************************************************
// THIS PAGE IS A REGISTRATION PAGE AND HAS A SCHEDULE
//*************************************************************************
$query_getRegPages = "SELECT * FROM page_schedule WHERE page_id = '$page_id' AND reg_page='y'";
$getRegPages = mysqli_query($studioAdmin, $query_getRegPages) or die(db_error_handle());
$total_RegPages = mysqli_num_rows($getRegPages);
if($total_RegPages !=0) {
	$reg_content = $content;
	// Process the schedule on this page

	// Start checkbox numbering from 50 to make sure it doesn't overwrite any from page.
		$cboxnum = 50;
		
		while($row_getRegPages = mysql_fetch_assoc($getRegPages)) { //For each schedule on this page	

			$schedule = $row_getRegPages['schedule_no'];	
			
			// Query the sched_info table to get table properties
			$query_getSchedInfo = "SELECT * FROM sched_info WHERE schedule_no='$schedule'";
			$getSchedInfo = mysqli_query($studioAdmin, $query_getSchedInfo) or die(db_error_handle());
			$row_getSchedInfo = mysqli_fetch_assoc($getSchedInfo);
			$type = $row_getSchedInfo['sched_type'];
			$studio = $row_getSchedInfo['studio'];
			$table_width = $row_getSchedInfo['max_width'];
			$section_bg_color = $row_getSchedInfo['section_bg_color'];
			$section_text_style = $row_getSchedInfo['section_text_style'];
			$studio_text_style = $row_getSchedInfo['studio_text_style'];
			$class_text_style = $row_getSchedInfo['class_text_style'];
			$class_bg_color = $row_getSchedInfo['class_bg_color'];
		
		
			// Query schedule table for studios
			$query_getStudios = "SELECT DISTINCT studio FROM schedule WHERE schedule_no='$schedule' AND status='A' AND studio != '' ORDER BY studio";
			$getStudios = mysqli_query($studioAdmin, $query_getStudios) or die(db_error_handle());
			$totalRows_getStudios = mysqli_num_rows($getStudios);

			// Set number of studios
			if($totalRows_getStudios > 1) {
				$numStudios = $totalRows_getStudios;
			} else {
				$numStudios = 1;
			}
			
			// Create array to save schedule element
			$reg_sched = array();
			
			// For each studio or just once if no studios found
			for($i=1; $i < $numStudios +1; $i++) { 
				if($numStudios > 1) {	
					// Select all class records from schedule table in the studio database and have studio
					$studioSet = mysqli_fetch_assoc($getStudios);
					$studioName = $studioSet['studio'];
					$query_getRegSchedule = "SELECT day,class_start_h, class_start_m, class_end_h, class_end_m,am_pm,name,ages,teacher,studio,hour24,dayno FROM schedule WHERE schedule_no='$schedule' AND status='A' AND studio='$studioName' ORDER BY dayno,hour24";
				} else {
					$query_getRegSchedule = "SELECT day,class_start_h, class_start_m, class_end_h, class_end_m,am_pm,name,ages,teacher,studio,hour24,dayno FROM schedule WHERE schedule_no='$schedule' AND status='A' ORDER BY dayno,hour24";
				}
				
				$getRegSchedule = mysqli_query($studioAdmin, $query_getRegSchedule) or die(db_error_handle());
				$totalRows_getRegSchedule = mysqli_num_rows($getRegSchedule);
			
				// Selects the distinct days on which there are classes
				$query_getDays = "SELECT day, dayno FROM schedule WHERE schedule_no='$schedule' AND status='A' GROUP BY day ORDER BY dayno";
				$getDays = mysqli_query($studioAdmin, $query_getDays) or die(db_error_handle());
				$totalRows_getDays = mysqli_num_rows($getDays);
			
				//Query pages table to get page file name
				$query_getRegFileName = "SELECT * FROM pages WHERE page_id='$page_id'";
				$getRegFileName = mysqli_query($studioAdmin, $query_getRegFileName) or die(db_error_handle());
				$rows_getRegFileName = mysqli_fetch_assoc($getRegFileName);
				$reg_filename = $rows_getRegFileName['file'];
				$pagename = $rows_getRegFileName['name'];
				$pagetitle = $rows_getFileName['title'];
				$_SESSION['page_title'] = $pagetitle;
			
				$daysofweek = array();
				while($row = mysqli_fetch_assoc($getDays))
				{
					$daysofweek[] = $row['day'];
				}
		
				// Build table and populate first row with day names
				
				$reg_sched[] = "<table width='500' border='1' align='center' cellpadding='4' cellspacing='0'>";
			
				// If there are studios to be displayed - create studio row
				if($numStudios > 1) {
					$reg_sched[] = "<tr><td colspan='2'>" . $studioName . "</td></tr>";
				}
				// Fetch the first record in recordset
				$row = mysqli_fetch_assoc($getRegSchedule);
			
				// Create cell (column) for each day of the week that has a class in the header row
				foreach($daysofweek as $days)
				{
					if($row['day'] == $days) {
						$reg_sched[] = "<tr><td align='left' bgcolor='" . $section_bg_color . "' colspan='2' class='" . $section_text_style . "'>" . $days . "</td></tr>";
						while($row['day'] == $days)
						{
							$d = $row['day'];
							$starttime_h = $row['class_start_h'];
							$starttime_m = $row['class_start_m'];
							$starttime = $starttime_h . ":" . $starttime_m;
							$endtime_h = $row['class_end_h'];
							$endtime_m = $row['class_end_m'];
							$endtime = $endtime_h . ":" . $endtime_m;
							$classname = $row['name'];
							$teacher = $row['teacher'];
							if($teacher == 'FULL'){
								$teacher = "<span style='color:#FF0000; font-weight:bold;'>FULL</span>"; }
							if($teacher != '') $teacher = " - " . $teacher;
							$find = array('/',' ');
							$format_name = str_replace($find,'_', $classname);
							$classall = $d . "_" . $starttime . "_" . $format_name;
							$classstr = $starttime . ' - ' . $endtime . ' ' . $classname  . $teacher;
							$reg_sched[] = '<tr bgcolor="' . $class_bg_color . '"><td align="left"><input type="checkbox" value="' . $classall . '" name="checkbox' . $cboxnum . '"/></td><td align="left" class="' . $class_text_style . '">' . $classstr .'</td></tr>';
							$row = mysqli_fetch_assoc($getRegSchedule); // Get next class
							$cboxnum += 1;
						}
					}
				}
				$reg_sched[] = '</table><p>&nbsp;</p>';
		}
		// Build registration table
		foreach($reg_sched as $t) {$reg_matrix .= $t;}
						
		//$reg_str = '<table id="schedulePH' . $schedule . '"><tbody><tr><td valign="top">The Class Schedule Will Go Here<br /><strong>DO NOT EDIT OR CHANGE THIS BOX IN ANY WAY</strong></td></tr></tbody></table>';
		
		$reg_str = '[[schedule' . $schedule . ']]';

		// Search and replace schedule place holder with table	
		$reg_contents_with_reg_matrix = str_ireplace($reg_str,$reg_matrix,$reg_content);
		$reg_matrix = "";
		$reg_content = $reg_contents_with_reg_matrix;
	}
	?>
	<div onclick="self.close();" style="background-color:#FFFFCC; border:thin; border-width:2px; border-style:solid; width:200px; padding:10px; margin-right:30px; margin-left:600px; top:30px; position:absolute;">Close this preview window</div>

	<?php


	include($header_file);
	echo stripslashes($reg_content);
	include($footer_file);
	exit;
}

?>