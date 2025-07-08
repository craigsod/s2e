<?php
include_once("schedule_builders.php");

if($target == 'schedule_no') { // If coming from the schedule editor
	if($target == 'schedule_no' && $totalRecords == 0) { // Make sure a valid schedule number was passed
		echo "That schedule does not exist";
		exit;
	}
	
	while($row_getRecords = mysqli_fetch_assoc($getRecords)) {
		$page_id = $row_getRecords['page_id'];
		// Query for contents
		$query_getContent = "SELECT * FROM content WHERE page_id = '$page_id' AND status = 'A'";
		$getContent = mysqli_query($studioAdmin, $query_getContent);
		$row_getContent = mysqli_fetch_assoc($getContent);
		$content = $row_getContent['contents'];
		
		
		//***********************************************************************
		// PROCESS SCHEDULE PAGES WITH SCHEDULES - COMING FROM SCHEDULE EDITOR
		//***********************************************************************
		$query_getPages = "SELECT * FROM page_schedule WHERE page_id = '$page_id' AND reg_page='n'";
		$getPages = mysqli_query($studioAdmin, $query_getPages) or die(db_error_handle());
		$total_Pages = mysqli_num_rows($getPages);

		if($total_Pages !=0) {
		//Worked here
			$sched_content = $content;
			//Query pages table to get page file name
			$query_getFileName = "SELECT * FROM pages WHERE page_id='$page_id'";
			$getFileName = mysqli_query($studioAdmin, $query_getFileName);
			//Worked here
			$rows_getFileName = mysqli_fetch_assoc($getFileName);
			$filename = "../" . $rows_getFileName['file'];
			$pagename = $rows_getFileName['name'];
			$pagetitle = $rows_getFileName['title'];
			$_SESSION['page_title'] = $pagetitle;
			// Worked here
			// Process the schedule on this page
			while($row_getPages = mysqli_fetch_assoc($getPages)) { // For each schedule on this page
			//Worked here
				$schedule = $row_getPages['schedule_no'];
				// Check to see if there are any records in the schedule
				$query_scheduleRecords = "SELECT * FROM schedule WHERE schedule_no = '$schedule'";
				
				$getScheduleRecords = mysqli_query($studioAdmin, $query_scheduleRecords);
				//Failed
				$rows_getScheduleRecords = mysqli_num_rows($getScheduleRecords);

				//If no records - exit process
				if($rows_getScheduleRecords == 0) break 1;
				
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
			// Put placecode search and replce process here
			// Loop through all active placecodes and search $sched_content for any instances
			//      If found,
			// 			Replace placecode with code
			//			Update placecode_page table with placecode_id and page_id
			
			ob_start();
			include($header_file);
			echo stripslashes($sched_content);
			include($footer_file);
			$page = ob_get_contents();
			ob_end_clean();
			$file = fopen($filename . ".htm",'w');
			fputs($file, $page);
			fclose($file);
		}
		//*************************************************************************
		// PROCESS REGISTRATION PAGES WITH SCHEDULES - COMING FROM SCHEDULE EDITOR
		//*************************************************************************
		$query_getRegPages = "SELECT * FROM page_schedule WHERE page_id = '$page_id' AND reg_page='y'";
		$getRegPages = mysqli_query($studioAdmin, $query_getRegPages) or die(db_error_handle());
		$total_RegPages = mysqli_num_rows($getRegPages);
		
		if($total_RegPages !=0) {
			$reg_content = $content;
			// Process the schedule on this page

			// Start checkbox numbering from 50 to make sure it doesn't overwrite any from page.
			$cboxnum = 50;
			
			while($row_getRegPages = mysqli_fetch_assoc($getRegPages)) { //For each schedule on this page
				$schedule = $row_getRegPages['schedule_no'];
				// Check to see if there are any records in the schedule
				$query_scheduleRegRecords = "SELECT * FROM schedule WHERE schedule_no = '$schedule'";
				$getRegRecords = mysqli_query($studioAdmin, $query_scheduleRegRecords);
				$rows_getRegRecords = mysqli_num_rows($getRegRecords);

				//If no records - exit process
				if($rows_getRegRecords == 0) break;
				
				// Query the sched_info table to get table properties
				$query_getSchedInfo = "SELECT * FROM sched_info WHERE schedule_no='$schedule'";
				$getSchedInfo = mysqli_query($studioAdmin, $query_getSchedInfo);
				$row_getSchedInfo = mysqli_fetch_assoc($getSchedInfo);
				$type = $row_getSchedInfo['sched_type'];
				$studio = $row_getSchedInfo['studio'];
				$table_width = $row_getSchedInfo['max_width'];
				$section_bg_color = $row_getSchedInfo['section_bg_color'];
				$section_text_style = $row_getSchedInfo['section_text_style'];
				$studio_text_style = $row_getSchedInfo['studio_text_style'];
				$class_text_style = $row_getSchedInfo['class_text_style'];
			
				// Select all class records from schedule table in the studio database
				mysqli_select_db($studioAdmin, $database_studioAdmin);
				$query_getRegSchedule = "SELECT day,class_start_h, class_start_m, class_end_h, class_end_m,am_pm,name,ages,teacher,studio,hour24,dayno, class_status FROM schedule WHERE schedule_no='$schedule' AND status='A' ORDER BY dayno,hour24";
				$getRegSchedule = mysqli_query($studioAdmin,$query_getRegSchedule) or die(db_error_handle());
				$totalRows_getRegSchedule = mysqli_num_rows($getRegSchedule);
				
				// Selects the distinct days on which there are classes
				mysqli_select_db($studioAdmin, $database_studioAdmin);
				$query_getDays = "SELECT day, dayno FROM schedule WHERE schedule_no='$schedule' AND status='A' GROUP BY day ORDER BY dayno";
				$getDays = mysqli_query($studioAdmin, $query_getDays);
				$totalRows_getDays = mysqli_num_rows($getDays);
				
				//Query pages table to get page file name
				$query_getRegFileName = "SELECT * FROM pages WHERE page_id='$page_id'";
				$getRegFileName = mysqli_query($studioAdmin, $query_getRegFileName) or die(db_error_handle());
				$rows_getRegFileName = mysqli_fetch_assoc($getRegFileName);
				$reg_filename = "../" . $rows_getRegFileName['file'];
				$pagename = $rows_getRegFileName['name'];
				$pagetitle = $rows_getFileName['title'];
				$_SESSION['page_title'] = $pagetitle;
				
				$daysofweek = array();
				while($row = mysqli_fetch_assoc($getDays))
				{
					$daysofweek[] = $row['day'];
				}
			
				// Build table and populate first row with day names
				$reg_sched = array();
				$reg_sched[] = "<table width='500' border='1' align='center' cellpadding='4' cellspacing='0'>";
				
				// Fetch the first record in recordset
				$row = mysqli_fetch_assoc($getRegSchedule);
				
				// Create cell (column) for each day of the week that has a class in the header row
			foreach($daysofweek as $days)
			{
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
					$class_status = $row['class_status'];
					if($class_status == 'FULL') {
						$classstatus = "<span style='color:#FF0000; font-weight:bold;'>  FULL  </span>";
					} else { $classstatus = '';}
					$find = array('/',' ');
					$format_name = str_replace($find,'_', $classname);
					$classall = $d . "_" . $starttime . "_" . $format_name;
					$classstr = $starttime . ' - ' . $endtime . ' ' . $classname . ' - ' . $teacher . ' ' . $classstatus;
					if($class_status == 'FULL') {
						$reg_sched[] = '<tr><td class="' . $class_text_style . '" align="left">&nbsp;</td><td align="left" class="text">' . $classstr .'</td></tr>';
					} else {
						$reg_sched[] = '<tr><td class="' . $class_text_style . '" align="left"><input type="checkbox" value="' . $classall . '" name="checkbox' . $cboxnum . '"/></td><td align="left" class="text">' . $classstr .'</td></tr>';
					}
					$row = mysqli_fetch_assoc($getRegSchedule); // Get next class
					$cboxnum += 1;
				}
			}
				$reg_sched[] = '</table>';
				// Build registration table
				foreach($reg_sched as $t) {$reg_matrix .= $t;}
								
				$reg_str = '[[schedule' . $schedule . ']]';

				// Search and replace schedule place holder with table	
				$reg_contents_with_reg_matrix = str_ireplace($reg_str,$reg_matrix,$reg_content);
				$reg_matrix = "";
				$reg_content = $reg_contents_with_reg_matrix;
			}
			

			// Build static HTML page
			ob_start();
			include($header_file);
			echo stripslashes($reg_content);
			include($footer_file);
			$reg_page = ob_get_contents();
			ob_end_clean();
			$reg_file = fopen($reg_filename . ".htm",'w');
			fputs($reg_file, $reg_page);
			fclose($reg_file);
		}
			
	}
	header("Location: $returnto");
} elseif($target == 'page_id') { 
	// *****************************************************************
	// IF CHANGE IS COMING FROM CONTENT EDITOR
	//******************************************************************
	// Process to handle a specific page that was edited and has a schedules on it
	$page_id = $search;

	// Query for page contents
	$query_getContent = "SELECT * FROM content WHERE page_id = '$page_id' AND status = 'A'";
	$getContent = mysqli_query($studioAdmin, $query_getContent);
	$row_getContent = mysqli_fetch_assoc($getContent);
	$content = $row_getContent['contents'];
	
	// ****************************************************************
	// PROCESS SCHEDULE PAGES WITH SCHEDULES
	$query_getSchedules = "SELECT * FROM page_schedule WHERE page_id = '$page_id' AND reg_page='n'";
	$getSchedules = mysqli_query($studioAdmin, $query_getSchedules);
	$total_Schedules = mysqli_num_rows($getSchedules);
	if($total_Schedules !=0) {
		$sched_content = $content;
		//Query pages table to get page file name
		$query_getFileName = "SELECT * FROM pages WHERE page_id='$page_id'";
		$getFileName = mysqli_query($studioAdmin, $query_getFileName);
		$rows_getFileName = mysqli_fetch_assoc($getFileName);
		$filename = "../" . $rows_getFileName['file'];
		$pagename = $rows_getFileName['name'];	
		$pagetitle = $rows_getFileName['title'];
		$_SESSION['page_title'] = $pagetitle;
		
		// Process the schedule on this page
		while($row_getSchedules = mysqli_fetch_assoc($getSchedules)) {
			$schedule = $row_getSchedules['schedule_no'];
			// Check to see if there are any records in the schedule
			$query_scheduleRecords = "SELECT * FROM schedule WHERE schedule_no = '$schedule'";
			$getRecords = mysqli_query($studioAdmin, $query_scheduleRecords);
			$rows_getRecords = mysqli_num_rows($getRecords);

			//If no records - exit process
			if($rows_getRecords != 0) {
			
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
		
				//$str = '<table cellspacing="1" cellpadding="1" border="1" width="400" id="schedulePH' . $schedule . '"><tbody><tr><td>Schedule Goes Here</td></tr></tbody></table>';
				
				$str ='[[schedule' . $schedule . ']]';
				
				$contents_with_matrix = str_ireplace($str,$matrix,$sched_content);
				$matrix = "";
				$sched_content = $contents_with_matrix;
			} elseif($rows_getRecords ==0) {
				$str = '[[schedule' . $schedule . ']]';
				
				$contents_with_matrix = str_ireplace($str,"<p>$nbsp;</p>",$sched_content);
				$matrix = "";
				$sched_content = $contents_with_matrix;
			}
		}
		ob_start();
		include($header_file);
		echo stripslashes($sched_content);
		include($footer_file);
		$page = ob_get_contents();
		ob_end_clean();
		$file = fopen($filename . ".htm",'w');
		fputs($file, $page);
		fclose($file);
	}
	
	//***********************************************
	// PROCESSS REGISTRATION PAGES WITH SCHEDULES
	//***********************************************
	$query_getSchedules = "SELECT * FROM page_schedule WHERE page_id = '$page_id' AND reg_page='y'";
	$getSchedules = mysqli_query($studioAdmin, $query_getSchedules);
	$total_Schedules = mysqli_num_rows($getSchedules);
	if($total_Schedules !=0) {
		$reg_content = $content;
		
		while($row_getSchedules = mysqli_fetch_assoc($getSchedules)) {
			$schedule = $row_getSchedules['schedule_no'];
			// Check to see if there are any records in the schedule
			$query_scheduleRecords = "SELECT * FROM schedule WHERE schedule_no = '$schedule'";
			$getRecords = mysqli_query($studioAdmin, $query_scheduleRecords);
			$rows_getRecords = mysqli_num_rows($getRecords);
			//If no records - exit process
			if($rows_getRecords == 0) break;
			
			// Query the sched_info table to get table properties
			$query_getSchedInfo = "SELECT * FROM sched_info WHERE schedule_no='$schedule'";
			$getSchedInfo = mysqli_query($studioAdmin, $query_getSchedInfo);
			$row_getSchedInfo = mysqli_fetch_assoc($getSchedInfo);
			$type = $row_getSchedInfo['sched_type'];
			$studio = $row_getSchedInfo['studio'];
			$table_width = $row_getSchedInfo['max_width'];
			$section_bg_color = $row_getSchedInfo['section_bg_color'];
			$section_text_style = $row_getSchedInfo['section_text_style'];
			$studio_text_style = $row_getSchedInfo['studio_text_style'];
			$class_text_style = $row_getSchedInfo['class_text_style'];

			// Select all class records from schedule table in the studio database
			mysqli_select_db($studioAdmin, $database_studioAdmin);
			$query_getSchedule = "SELECT day,class_start_h, class_start_m, class_end_h, class_end_m,am_pm,name,ages,teacher,studio,hour24,dayno,class_status FROM schedule WHERE schedule_no='$schedule' AND status='A' ORDER BY dayno,hour24";
			$getSchedule = mysqli_query($studioAdmin, $query_getSchedule) or die(db_error_handle());
			$totalRows_getSchedule = mysqli_num_rows($getSchedule);
			
			// Selects the distinct days on which there are classes
			mysqli_select_db($studioAdmin, $database_studioAdmin);
			$query_getDays = "SELECT day, dayno FROM schedule WHERE schedule_no='$schedule' AND status='A' GROUP BY day ORDER BY dayno";
			$getDays = mysqli_query($studioAdmin, $query_getDays);
			$totalRows_getDays = mysqli_num_rows($getDays);
			
			//Query pages table to get page file name
			$query_getRegFileName = "SELECT * FROM pages WHERE page_id='$page_id'";
			$getRegFileName = mysqli_query($studioAdmin, $query_getRegFileName) or die(db_error_handle());
			$rows_getRegFileName = mysqli_fetch_assoc($getRegFileName);
			$reg_filename = "../" . $rows_getRegFileName['file'];
			$pagename = $rows_getRegFileName['name'];
			
			$daysofweek = array();
			while($row = mysqli_fetch_assoc($getDays))
			{
				$daysofweek[] = $row['day'];
			}
		
			// Build table and populate first row with day names
			$reg_sched = array();
			$reg_sched[] = "<table align='center' width='500' border='1' cellpadding='4' cellspacing='0'>";
			
			// Fetch the first record in recordset
			$row = mysqli_fetch_assoc($getSchedule);
			
			// Start checkbox numbering from 40
			$cboxnum = 50;
			
			// Create cell (column) for each day of the week that has a class in the header row
			foreach($daysofweek as $days)
			{
				$reg_sched[] = "<tr><td align='left' colspan='2' bgcolor='" . $section_bg_color . "' class='" . $section_text_style . "'>" . $days . "</td></tr>";
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
					$class_status = $row['class_status'];
					if($class_status == 'FULL') {
						$classstatus = "<span style='color:#FF0000; font-weight:bold;'>  FULL  </span>";
					} else {
						$classstatus = '';
					}
					$find = array('/',' ');
					$format_name = str_replace($find,'_', $classname);
					$classall = $d . "_" . $starttime . "_" . $format_name;
					$classstr = $starttime . ' - ' . $endtime . ' ' . $classname . ' - ' . $teacher . ' ' . $classstatus;
					if($class_status == 'FULL') {
						$reg_sched[] = '<tr><td class="' . $class_text_style . '" align="left">&nbsp;</td><td align="left" class="text">' . $classstr .'</td></tr>';
					} else {
						$reg_sched[] = '<tr><td class="' . $class_text_style . '" align="left"><input type="checkbox" value="' . $classall . '" name="checkbox' . $cboxnum . '"/></td><td align="left" class="text">' . $classstr .'</td></tr>';
					}
					$row = mysqli_fetch_assoc($getSchedule); // Get next class
					$cboxnum += 1;
				}
			}
			$reg_sched[] = '</table>';
			$reg_sched[] = '<br>';
			// Build registration table
			foreach($reg_sched as $t) {$reg_matrix .= $t;}
		
			$reg_str = '[[schedule' . $schedule . ']]';
			// Search and replace schedule place holder with table	
			$reg_contents_with_reg_matrix = str_ireplace($reg_str,$reg_matrix,$reg_content);
			$reg_matrix = "";
			$reg_content = $reg_contents_with_reg_matrix;
		}
		
		// Build static HTML page
		ob_start();
		include($header_file);
		echo stripslashes($reg_content);
		include($footer_file);
		$reg_page = ob_get_contents();
		ob_end_clean();
		$reg_file = fopen($reg_filename . ".htm",'w');
		fputs($reg_file, $reg_page);
		fclose($reg_file);
	}
}
header("Location: $returnto");
?>