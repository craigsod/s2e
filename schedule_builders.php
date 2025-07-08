<?php
//*********************************************************
// Fucntion to create an across type schedule table
//*********************************************************
function schedule_across($schedule_no,$studioAdmin, $getSchedInfo, $row_getSchedInfo)
{
	$table_width = $row_getSchedInfo['max_width'];
	$section_bg_color = $row_getSchedInfo['section_bg_color'];
	$section_text_style = $row_getSchedInfo['section_text_style'];
	$studio_text_style = $row_getSchedInfo['studio_text_style']; 
	$border_color = $row_getSchedInfo['border_color'];
	$class_brdr_color = $row_getSchedInfo['class_brdr_color'];
	$class_bg_color = $row_getSchedInfo['class_bg_color'];
	$class_text_style = $row_getSchedInfo['class_text_style'];
	
	//echo $section_bg_color . "<br>" . $section_text_style . "<br>" . $studio_text_style . "<br>" . $border_color . "<br>" . $class_brdr_color . "<br>" . $class_bg_color;
	//exit;
	
	// Selects the distinct days on which there are classes
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$query_getDays = "SELECT day, dayno FROM schedule WHERE schedule_no='$schedule_no' AND status='A' GROUP BY day ORDER BY dayno";
	$getDays = mysqli_query($studioAdmin,$query_getDays) or die(db_error_handle());
	$totalRows_getDays = mysqli_num_rows($getDays);
	
	while($row = mysqli_fetch_assoc($getDays))
	{
		$daysofweek[] = $row['day'];
	}
	$count_of_days = count($daysofweek);
	$width_of_days = $table_width / $count_of_days;
	
	// Create array with contents of Sched Info table
	$schedInfo = mysqli_fetch_assoc($getSchedInfo);
	
	// Delete the Day field and values so it doesn't go to the schedule
	unset($schedInfo['day']);
	
	// Search the schedule specs array for items to be displayed on schedule
	$keys = array_keys($schedInfo, 'y');
	
	// Get count of fields that will be included on schedule
	$keycount = count($keys);
	
	// Build table and populate first row with day names
	$matrix = array();
	$matrix[] = "<table align='center' width='" . $table_width . "' border='0' bordercolor='" . $border_color . "' cellspacing='0'><tr><td><table width='100%' border='1' bordercolor='" . $class_brdr_color . "' cellpadding='2' cellspacing='0' bgcolor='" . $class_bg_color . "'>";
	$matrix[] = "<tr bgcolor='" . $section_bg_color . "'>";
	
	// Create cell (column) for each day of the week that has a class in the header row
	foreach($daysofweek as $days)
	{
		$matrix[] = "<td width='" . $width_of_days . "' class='" . $section_text_style . "' align='center'>" . $days . "</td>";
	}
	$matrix[] = "</tr>";
	
	// Fetch the first record in recordset
	//$row = mysqli_fetch_assoc($getSchedule);
	
	// Begin looping through time slots
	$halfcount=1;
	$addtime = 30;
	for($timeslot = 700; $timeslot <= 2400; $timeslot += $addtime)
	{
		if($halfcount ==1) {
			$hightime = $timeslot + 30;
		} else {
			$hightime = $timeslot + 70;
		}
		mysqli_select_db($studioAdmin, $database_studioAdmin);
		$query_getSchedule = "SELECT day,class_start_h, class_start_m, class_end_h, class_end_m,am_pm,name,ages,teacher,studio,hour24,dayno,class_status, classHL FROM schedule WHERE schedule_no='$schedule_no' AND status='A' AND hour24>='$timeslot' AND hour24< '$hightime' ORDER BY dayno, hour24";
		$getSchedule = mysqli_query($studioAdmin,$query_getSchedule) or die(db_error_handle());
		$totalRows_getSchedule = mysqli_num_rows($getSchedule);
		
		if($totalRows_getSchedule !=0) {
			while($row = mysqli_fetch_assoc($getSchedule)) {
				
				//$matrix[] = "<tr><td>" . $timeslot . " - " . $addtime . " - " . $hightime . " - " . $totalRows_getSchedule . "</td></tr>";
				if($row['hour24'] >= $timeslot and $row['hour24'] < $hightime)
				{
					$matrix[] = "<tr>";
					foreach($daysofweek as $days)
					{
						if($row['day'] == $days and $row['hour24'] >= $timeslot and $row['hour24'] < $hightime)
						{
							$matrix[] = "<td width='" . $width_of_days . "' valign= 'top'><p class='" . $class_text_style . "'>";
							if($row['classHL'] == 'Bold') { $matrix[] = "<strong>";}
							foreach($keys as $key)
							{
									if($key == 'class_start_h') {
										$matrix[] = $row[$key] . ":";
									} elseif($key == 'class_start_m') {
										$matrix[] = $row[$key] . " - ";
									} elseif($key == 'class_end_h') {
										$matrix[] = $row[$key] . ":";
									} elseif($key == 'class_end_m') {
										$matrix[] = $row[$key] . "<br>";
									} elseif($row[$key] != '') {
										if($row[$key] == 'FULL') {
											$matrix[] = "<span style='color:#FF0000; font-weight:bold;'>FULL</span><br>";
										} else {
											if($key == 'name' && $row['class_status'] == 'FULL') {
												$fullclass = "<span style='color:#FF0000; font-weight:bold;'> FULL </span>";
											} else {
												$fullclass = '';
											}
											$matrix[] = $row[$key] . ' ' . $fullclass . "<br>";
										}
									}
							}
							if($row['classHL'] == 'Bold') { $matrix[] = "</strong>";}
							$row = mysqli_fetch_assoc($getSchedule); // Get next class
							//if the next class has the same day and time - create a new line and display class details
							if($row['classHL'] == 'Bold') { $matrix[] = "<strong>";}
							if($row['day'] == $days and $row['hour24'] >= $timeslot and $row['hour24'] < $hightime)
							{
								$matrix[] = "<br>";
								foreach($keys as $key)
								{
									if($key == 'class_start_h') {
										$matrix[] = $row[$key] . ":";
									} elseif($key == 'class_start_m') {
										$matrix[] = $row[$key] . " - ";
									} elseif($key == 'class_end_h') {
										$matrix[] = $row[$key] . ":";
									} elseif($key == 'class_end_m') {
										$matrix[] = $row[$key] . "<br>";
									} elseif($row[$key] != '') {
										if($row[$key] == 'FULL') {
											$matrix[] = "<span style='color:#FF0000; font-weight:bold;'>FULL</span><br>";
										} else {
											if($key == 'name' && $row['class_status'] == 'FULL') {
												$fullclass = "<span style='color:#FF0000; font-weight:bold;'> FULL </span>";
											} else {
												$fullclass = '';
											}
											$matrix[] = $row[$key] . ' ' . $fullclass . "<br>";
										}
									}
								}
								if($row['classHL'] == 'Bold') { $matrix[] = "</strong>";}
								$matrix[] = "</p></td>";
								$row = mysqli_fetch_assoc($getSchedule); // Get next class
							} else {
								$matrix[] = "</p></td>";
							}
						} else {
							$matrix[] = "<td>&nbsp;</td>";
						}
					}
					$matrix[] = "</tr>";
				}
			}
		}
		if($halfcount ==1) {
			$addtime = 30;
			$halfcount = 2;
		} else {
			$addtime = 70;
			$halfcount = 1;
		}
	//mysqli_data_seek($getSchedule);
	}
	$matrix[] = "</table></td></tr></table>";
	return $matrix;
}

//*********************************************************
// Fucntion to create a down type schedule table
//*********************************************************
function schedule_down($schedule_no,$studioAdmin, $getSchedInfo, $row_getSchedInfo)
{
	$table_width = $row_getSchedInfo['max_width'];
	$section_bg_color = $row_getSchedInfo['section_bg_color'];
	$section_text_style = $row_getSchedInfo['section_text_style'];
	$studio_text_style = $row_getSchedInfo['studio_text_style']; 
	$border_color = $row_getSchedInfo['border_color'];
	$class_brdr_color = $row_getSchedInfo['class_brdr_color'];
	$class_bg_color = $row_getSchedInfo['class_bg_color'];
	$class_text_style = $row_getSchedInfo['class_text_style'];
	
	// Select all class records from schedule table in the studio database
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$query_getSchedule = "SELECT day,class_start_h, class_start_m, class_end_h, class_end_m,am_pm,name,ages,teacher,studio,hour24,dayno, class_status, classHL FROM schedule WHERE schedule_no='$schedule_no' AND status='A' ORDER BY dayno,hour24";
	$getSchedule = mysqli_query($studioAdmin,$query_getSchedule) or die(db_error_handle());
	$totalRows_getSchedule = mysqli_num_rows($getSchedule);
	
	// Selects the distinct days on which there are classes
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$query_getDays = "SELECT day, dayno FROM schedule WHERE schedule_no='$schedule_no' AND status='A' GROUP BY day ORDER BY dayno";
	$getDays = mysqli_query($studioAdmin,$query_getDays) or die(db_error_handle());
	$totalRows_getDays = mysqli_num_rows($getDays);

	// Populate schedule array with contents of recordset
	while($row = mysqli_fetch_assoc($getSchedule))
	{
		$schedule[] = $row;
	}
	
	// Populates array with key=>value pairs from recordset
	$schedInfo = mysqli_fetch_assoc($getSchedInfo);
	
	// Search the schedule speccs array for items to be displayed on schedule
	$keys = array_keys($schedInfo, 'y');
	array_shift($keys);
	
	// Get count of fields to be displayed minus one for the Day field
	$keycount = count($keys) + 1;
	
	$matrix = array();
	$matrix[] = "<table align='center' width='" . $table_width . "' border='1' align='left' cellpadding='4' cellspacing='0'>";
	
	// Start of main loop - For each day that has classes
	while($rowdays = mysqli_fetch_array($getDays))
	{
		$day_classes = array();
		$i = 0;
		foreach($schedule as $class)
		{
			if($class['day'] == $rowdays['day'])
			{
				$day_classes[$i] = $class;
				$i += 1;
			}
		}
		$matrix[] = "<tr bgcolor='" . $section_bg_color . "'><td align='left' colspan=" . $keycount . " class='" . $section_text_style . "'>" . strtoupper($rowdays['day']) . "</td></tr>";
		foreach($day_classes as $dc)
		{
			$matrix[] = "<tr bgcolor='" . $class_bg_color . "'>";
			foreach($keys as $key)
			{
				if($dc[$key] == NULL)
				{
					$matrix[] = "<td>&nbsp;</td>";
				} else {
					if($dc['classHL'] == 'Bold') 
					{ 
						if($key == 'class_start_h') {
							$matrix[] = "<td class='" . $class_text_style . "'><strong>" . $dc[$key] . ":";
						} elseif($key == 'class_start_m') {
							$matrix[] = $dc[$key] . " - ";
						} elseif($key == 'class_end_h') {
							$matrix[] = $dc[$key] . ":";
						} elseif($key == 'class_end_m') {
							$matrix[] = $dc[$key] . "</strong></td>";
						} elseif($dc[$key] != '') {
							if($dc[$key] == 'FULL') {
								$matrix[] = "<td style='color:#FF0000; font-weight:bold;'>FULL</td>";
							} else {
								if($key == 'name' && $dc['class_status'] == 'FULL') {
									$fullclass = "<span style='color:#FF0000; font-weight:bold;'> FULL </span>";
								} else {
									$fullclass = '';
								}
								$matrix[] = "<td class='" . $class_text_style . "'><strong>" . $dc[$key] . "  " . $fullclass . "</strong></td>";
							}
						}
					} else {
						if($key == 'class_start_h') {
							$matrix[] = "<td class='" . $class_text_style . "'>" . $dc[$key] . ":";
						} elseif($key == 'class_start_m') {
							$matrix[] = $dc[$key] . " - ";
						} elseif($key == 'class_end_h') {
							$matrix[] = $dc[$key] . ":";
						} elseif($key == 'class_end_m') {
							$matrix[] = $dc[$key] . "</td>";
						} elseif($dc[$key] != '') {
							if($dc[$key] == 'FULL') {
								$matrix[] = "<td style='color:#FF0000; font-weight:bold;'>FULL</td>";
							} else {
								if($key == 'name' && $dc['class_status'] == 'FULL') {
									$fullclass = "<span style='color:#FF0000; font-weight:bold;'> FULL </span>";
								} else {
									$fullclass = '';
								}
								$matrix[] = "<td class='" . $class_text_style . "'>" . $dc[$key] . "  " . $fullclass . "</td>";
							}
						}
					}
				}
			}
			$matrix[] = "</tr>";
		}
	}
	
	$matrix[] = "</table>";
	return $matrix;
}

//***************************************************************
// Fucntion to create an across type with studio schedule table
//***************************************************************
function schedule_across_studio($schedule_no,$studioAdmin, $getSchedInfo, $row_getSchedInfo)
{

	$table_width = $row_getSchedInfo['max_width'];
	$section_bg_color = $row_getSchedInfo['section_bg_color'];
	$section_text_style = $row_getSchedInfo['section_text_style'];
	$studio_text_style = $row_getSchedInfo['studio_text_style']; 
	$border_color = $row_getSchedInfo['border_color'];
	$class_brdr_color = $row_getSchedInfo['class_brdr_color'];
	$class_bg_color = $row_getSchedInfo['class_bg_color'];
	$class_text_style = $row_getSchedInfo['class_text_style'];
	
	// Selects the distinct days on which there are classes
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$query_getDays = "SELECT day, dayno FROM schedule WHERE schedule_no='$schedule_no' AND status='A' GROUP BY day ORDER BY dayno";
	$getDays = mysqli_query($studioAdmin, $query_getDays) or die(db_error_handle());
	$totalRows_getDays = mysqli_num_rows($getDays);
	
	// Select the distinct studio names and does not include a blank studio name in case user forgets to enter one
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$query_getStudios = "SELECT DISTINCT studio FROM schedule WHERE schedule_no='$schedule_no' AND status='A' AND studio != '' ORDER BY studio";
	$getStudios = mysqli_query($studioAdmin, $query_getStudios) or die(db_error_handle());
	$totalRows_getStudios = mysqli_num_rows($getStudios);
	 
	// Create array with studio names
	while($rowstudio = mysqli_fetch_array($getStudios))
	{
		$studios[] = $rowstudio;
	}
	
	while($row = mysqli_fetch_assoc($getDays))
	{
		$daysofweek[] = $row['day'];
	}
	$count_of_days = count($daysofweek);
	$width_of_days = $table_width / $count_of_days;
	$daycount = count($daysofweek);
	
	// Create array with contents of Sched Info table
	$schedInfo = mysqli_fetch_assoc($getSchedInfo);
	
	// Delete the Day field and values so it doesn't go to the schedule
	unset($schedInfo['day']);
	
	// Search the schedule specs array for items to be displayed on schedule
	$keys = array_keys($schedInfo, 'y');
	//array_pop($keys);
	
	// Get count of fields that will be included on schedule
	$keycount = count($keys);
	$matrix = array();
	foreach($studios as $studio) {
		// Build table and populate first row with day names
		$studioname = $studio['studio'];
		
		$matrix[] = "<table align='center' width='" . $table_width . "' border='0' bordercolor='" . $border_color . "' cellspacing='0'><tr><td><table width='100%' border='1' bordercolor='" . $class_brdr_color . "' cellpadding='4' cellspacing='0' bgcolor='" . $class_bg_color . "'>";
	$matrix[] = "<tr bgcolor='" . $class_bg_color . "'><td class='" . $studio_text_style . "' align='left' colspan=" .$daycount. ">" . $studio['studio'] . "</td></tr>";
		$matrix[] = "<tr bgcolor='" . $section_bg_color . "'>";
		
		// Create cell (column) for each day of the week that has a class in the header row
		foreach($daysofweek as $days)
		{
			$matrix[] = "<td align='center' class='" . $section_text_style . "' align='center'>" . $days . "</td>";
		}
		$matrix[] = "</tr>";
		
		// Fetch the first record in recordset
		//$row = mysqli_fetch_assoc($getSchedule);
		
		// Begin looping through time slots
		$halfcount=1;
		$addtime = 30;
		for($timeslot = 700; $timeslot <= 2400; $timeslot += $addtime)
		{
			if($halfcount ==1) {
				$hightime = $timeslot + 30;
			} else {
				$hightime = $timeslot + 70;
			}
			mysqli_select_db($studioAdmin, $database_studioAdmin);
			$query_getSchedule = "SELECT day,class_start_h, class_start_m, class_end_h, class_end_m,am_pm,name,ages,teacher,studio,hour24,dayno, classHL FROM schedule WHERE schedule_no='$schedule_no' AND status='A' AND hour24>='$timeslot' AND hour24< '$hightime' AND studio='$studioname' ORDER BY studio,dayno, hour24";
			$getSchedule = mysqli_query($studioAdmin,$query_getSchedule) or die(db_error_handle());
			$totalRows_getSchedule = mysqli_num_rows($getSchedule);
			
			if($totalRows_getSchedule !=0) {
				while($row = mysqli_fetch_assoc($getSchedule)) {
					
					//$matrix[] = "<tr><td>" . $timeslot . " - " . $addtime . " - " . $hightime . " - " . $totalRows_getSchedule . "</td></tr>";
					if($row['hour24'] >= $timeslot and $row['hour24'] < $hightime)
					{
						$matrix[] = "<tr>";
						foreach($daysofweek as $days)
						{
							if($row['day'] == $days and $row['hour24'] >= $timeslot and $row['hour24'] < $hightime)
							{
								$matrix[] = "<td width='" . $width_of_days . "' valign= 'top'><p class='" . $class_text_style . "'>";
								if($row['classHL'] == 'Bold') { $matrix[] = "<strong>";}
								foreach($keys as $key)
								{
										if($key == 'class_start_h') {
											$matrix[] = $row[$key] . ":";
										} elseif($key == 'class_start_m') {
											$matrix[] = $row[$key] . " - ";
										} elseif($key == 'class_end_h') {
											$matrix[] = $row[$key] . ":";
										} elseif($key == 'class_end_m') {
											$matrix[] = $row[$key] . "<br>";
										} elseif($row[$key] != '') {
											if($row[$key] == 'FULL') {
												$matrix[] = "<span style='color:#FF0000; font-weight:bold;'>FULL</span><br>";
											} else {
											if($key == 'name' && $row['class_status'] == 'FULL') {
												$fullclass = "<span style='color:#FF0000; font-weight:bold;'> FULL </span>";
											} else {
												$fullclass = '';
											}
											$matrix[] = $row[$key] . ' ' . $fullclass . "<br>";
											}
										}
								}
								if($row['classHL'] == 'Bold') { $matrix[] = "</strong>";}
								$row = mysqli_fetch_assoc($getSchedule); // Get next class
								//if the next class has the same day and time - create a new line and display class details
								if($row['day'] == $days and $row['hour24'] >= $timeslot and $row['hour24'] < $hightime)
								{
									if($row['classHL'] == 'Bold') { $matrix[] = "<strong>";}
									$matrix[] = "<br>";
									foreach($keys as $key)
									{
										if($key == 'class_start_h') {
											$matrix[] = $row[$key] . ":";
										} elseif($key == 'class_start_m') {
											$matrix[] = $row[$key] . " - ";
										} elseif($key == 'class_end_h') {
											$matrix[] = $row[$key] . ":";
										} elseif($key == 'class_end_m') {
											$matrix[] = $row[$key] . "<br>";
										} elseif($row[$key] != '') {
											if($row[$key] == 'FULL') {
												$matrix[] = "<span style='color:#FF0000; font-weight:bold;'>FULL</span>";
											} else {
											if($key == 'name' && $row['class_status'] == 'FULL') {
												$fullclass = "<span style='color:#FF0000; font-weight:bold;'> FULL </span>";
											} else {
												$fullclass = '';
											}
											$matrix[] = $row[$key] . ' ' . $fullclass . "<br>";
											}
										}
									}
									$matrix[] = "</p></td>";
									$row = mysqli_fetch_assoc($getSchedule); // Get next class
									if($row['classHL'] == 'Bold') { $matrix[] = "</strong>";}
								} else {
									$matrix[] = "</p></td>";
								}
							} else {
								$matrix[] = "<td>&nbsp;</td>";
							}
						}
						$matrix[] = "</tr>";
					}
				}
			}
			if($halfcount ==1) {
				$addtime = 30;
				$halfcount = 2;
			} else {
				$addtime = 70;
				$halfcount = 1;
			}
		//mysqli_data_seek($getSchedule);
		}
		$matrix[] = "</table></table>";
		$matrix[] = "<p>&nbsp;</p>";
	}
	return $matrix;
}

//***********************************************************
// Fucntion to create a down type with studio schedule table
//***********************************************************
function schedule_down_studio($schedule_no,$studioAdmin, $getSchedInfo, $row_getSchedInfo)
{

	$table_width = $row_getSchedInfo['max_width'];
	$section_bg_color = $row_getSchedInfo['section_bg_color'];
	$section_text_style = $row_getSchedInfo['section_text_style'];
	$studio_text_style = $row_getSchedInfo['studio_text_style']; 
	$border_color = $row_getSchedInfo['border_color'];
	$class_brdr_color = $row_getSchedInfo['class_brdr_color'];
	$class_bg_color = $row_getSchedInfo['class_bg_color'];
	$class_text_style = $row_getSchedInfo['class_text_style'];
	
	// Select all class records from schedule table in the studio database
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$query_getSchedule = "SELECT day,class_start_h, class_start_m, class_end_h, class_end_m,am_pm,name,ages,teacher,studio,hour24,dayno, classHL FROM schedule WHERE schedule_no='$schedule_no' AND status='A' ORDER BY dayno,hour24,studio";
	$getSchedule = mysqli_query($studioAdmin,$query_getSchedule) or die(db_error_handle());
	$totalRows_getSchedule = mysqli_num_rows($getSchedule);
	
	// Selects the distinct days on which there are classes
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$query_getDays = "SELECT day, dayno FROM schedule WHERE schedule_no='$schedule_no' AND status='A' GROUP BY day ORDER BY dayno";
	$getDays = mysqli_query($studioAdmin,$query_getDays) or die(db_error_handle());
	$totalRows_getDays = mysqli_num_rows($getDays);
	
		// Select the distinct studio names and does not include a blank studio name in case user forgets to enter one
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$query_getStudios = "SELECT DISTINCT studio FROM schedule WHERE schedule_no='$schedule_no' AND status='A' AND studio != '' ORDER BY studio";
	$getStudios = mysqli_query($studioAdmin,$query_getStudios) or die(db_error_handle());
	$totalRows_getStudios = mysqli_num_rows($getStudios);
	 
	// Create array with studio names
	while($rowstudio = mysqli_fetch_array($getStudios))
	{
		$studios[] = $rowstudio;
	}
	
	// Populate schedule array with contents of recordset
	while($row = mysqli_fetch_assoc($getSchedule))
	{
		$schedule[] = $row;
	}
	
	// Populates array with key=>value pairs from recordset
	$schedInfo = mysqli_fetch_assoc($getSchedInfo);
	
	// Search the schedule specs array for items to be displayed on schedule
	$keys = array_keys($schedInfo, 'y');
	// Remove the day field from the list of fields values so it is not displayed
	array_shift($keys);
	
	// Get count of fields to be displayed minus one for the Day field
	$keycount = count($keys) + 1;
	
	for($i = 0; $i < 2; $i++)
	{
		$matrix = array();
		$matrix[] = "<table width='" . $table_width . "' border='1' bordercolor='" . $border_color . "' align='center' cellpadding='4' cellspacing='0'>";
	
		// Start of main loop - For each day that has classes
		while($rowdays = mysqli_fetch_array($getDays))
		{
			//Create an array to hold all the classes that occur on this day
			$day_classes = array();
			$i = 0;
			foreach($schedule as $class)
			{
				if($class['day'] == $rowdays['day'])
				{
					// If the class is on this day - add it to the array
					$day_classes[$i] = $class;
					$i += 1;
				}
			}
			$matrix[] = "<tr bgcolor='" . $section_bg_color . "'><td align='left' colspan=" . $keycount . " class='" . $section_text_style . "'>" . strtoupper($rowdays['day']) . "</td></tr>";
			foreach($studios as $studio)
			{
				$studio_class = array();
				$i2 = 0;
				foreach($day_classes as $dc)
				{
					if($dc['studio'] == $studio['studio'])
					{
						$studio_class[$i2] = $dc;
						$i2 += 1;
					}
				}
				$classcount = count($studio_class);
				if($classcount != 0) {
					$matrix[] = "<tr bgcolor='" . $class_bg_color . "'><td rowspan = " . $classcount . " valign='top'><span class='" . $studio_text_style . "'>" . $studio['studio'] . "</span></td>";
					$i3 = 0;
					foreach($studio_class as $sc)
					{
						if($i3 >= 1)
						{
							$matrix[] = "<tr bgcolor='" . $class_bg_color . "'>";
						}
						foreach($keys as $key)
						{
							if($sc[$key] == NULL)
							{
								$matrix[] = "<td>&nbsp;</td>";
							} else {
								if($sc['classHL'] == 'Bold') 
								{ 
									if($key == 'class_start_h') {
										$matrix[] = "<td class='" . $class_text_style . "'><strong>" . $sc[$key] . ":";
									} elseif($key == 'class_start_m') {
										$matrix[] = $sc[$key] . " - ";
									} elseif($key == 'class_end_h') {
										$matrix[] = $sc[$key] . ":";
									} elseif($key == 'class_end_m') {
										$matrix[] = $sc[$key] . "</strong></td>";
									} elseif($sc[$key] != '') {
										if($sc[$key] == 'FULL') {
											$matrix[] = "<td style='color:#FF0000; font-weight:bold;'>FULL</td>";
										} else {
								if($key == 'name' && $sc['class_status'] == 'FULL') {
									$fullclass = "<span style='color:#FF0000; font-weight:bold;'> FULL </span>";
								} else {
									$fullclass = '';
								}
								$matrix[] = "<td class='" . $class_text_style . "'>" . $sc[$key] . "  " . $fullclass . "</td>";
								}
									}
								} else {
									if($key == 'class_start_h') {
										$matrix[] = "<td class='" . $class_text_style . "'>" . $sc[$key] . ":";
									} elseif($key == 'class_start_m') {
										$matrix[] = $sc[$key] . " - ";
									} elseif($key == 'class_end_h') {
										$matrix[] = $sc[$key] . ":";
									} elseif($key == 'class_end_m') {
										$matrix[] = $sc[$key] . "</td>";
									} elseif($sc[$key] != '') {
										if($sc[$key] == 'FULL') {
											$matrix[] = "<td style='color:#FF0000; font-weight:bold;'>FULL</td>";
										} else {
											
								if($key == 'name' && $sc['class_status'] == 'FULL') {
									$fullclass = "<span style='color:#FF0000; font-weight:bold;'> FULL </span>";
								} else {
									$fullclass = '';
								}
								$matrix[] = "<td class='" . $class_text_style . "'>" . $sc[$key] . "  " . $fullclass . "</td>";
								}
									}
								}
							}
						}
						$matrix[] = "</tr>";
						$i3 += 1;
					}
				}		
			}
		}
		$matrix[] = "</table>";
	}
	return $matrix;
}
//*********************************************************
// Fucntion to create a classes type schedule table
//*********************************************************
function schedule_classes($schedule_no,$studioAdmin, $getSchedInfo, $row_getSchedInfo)
{
	if(!function_exists(array_insert)) {
		function array_insert(&$array, $offset, $new)
		{
			array_splice($array, $offset, 0, $new);
		}
	}

	$table_width = $row_getSchedInfo['max_width'];
	$section_bg_color = $row_getSchedInfo['section_bg_color'];
	$section_text_style = $row_getSchedInfo['section_text_style'];
	$studio_text_style = $row_getSchedInfo['studio_text_style']; 
	$border_color = $row_getSchedInfo['border_color'];
	$class_brdr_color = $row_getSchedInfo['class_brdr_color'];
	$class_bg_color = $row_getSchedInfo['class_bg_color'];
	$class_text_style = $row_getSchedInfo['class_text_style'];
	
	// Select all class records from schedule table in the studio database
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$query_getSchedule = "SELECT day,class_start_h, class_start_m, class_end_h, class_end_m,am_pm,name,ages,teacher,studio,hour24,dayno,classHL FROM schedule WHERE schedule_no='$schedule_no' AND status='A' ORDER BY dayno,hour24";
	$getSchedule = mysqli_query($studioAdmin,$query_getSchedule) or die(db_error_handle());
	$totalRows_getSchedule = mysqli_num_rows($getSchedule);
	
	// Selects the distinct classes names
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$query_getNames = "SELECT DISTINCT name FROM schedule WHERE schedule_no='$schedule_no' AND status='A'";
	$getNames = mysqli_query($studioAdmin, $query_getNames) or die(db_error_handle());
	$totalRows_getDNames = mysqli_num_rows($getNames);
	
	// Selects the distinct days on which there are classes
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$query_getDays = "SELECT day, dayno FROM schedule WHERE schedule_no='$schedule_no' AND status='A' GROUP BY day ORDER BY dayno";
	$getDays = mysqli_query($studioAdmin, $query_getDays) or die(db_error_handle());
	$totalRows_getDays = mysqli_num_rows($getDays);
	
	// Populate schedule array with contents of recordset
	while($row = mysqli_fetch_assoc($getSchedule))
	{
		$schedule[] = $row;
	}
	// Populates array with key=>value pairs from recordset
	$schedInfo = mysqli_fetch_assoc($getSchedInfo);
	
	// Search the schedule speccs array for items to be displayed on schedule
	$keys = array_keys($schedInfo, 'y');
	array_insert($keys, 0, 'day');
	
	// Get count of fields to be displayed minus one for the Day field
	$keycount = count($keys) + 1;
	
	$matrix = array();
	$matrix[] = "<table width='" . $table_width . "' border='1' brdrcolor='" . $class_brdr_color . "' align='center' cellpadding='4' cellspacing='0'>";
	
	// Start of main loop - For each day that has classes
	while($rownames = mysqli_fetch_array($getNames))
	{
		$class_names = array();
		$i = 0;
		foreach($schedule as $class)
		{
			if($class['name'] == $rownames['name'])
			{
				$class_names[$i] = $class;
				$i += 1;
			}
		}
		$matrix[] = "<tr bgcolor='" . $section_bg_color . "'><td align='left' colspan=" . $keycount . " class='" . $section_text_style . "'>" . $rownames['name'] . "</td></tr>";
		foreach($class_names as $cn)
		{
			$matrix[] = "<tr bgcolor='". $class_bg_color . "'>";
			foreach($keys as $key)
			{
				if($cn[$key] == NULL)
				{
					$matrix[] = "<td bgcolor='". $class_bg_color . "'>&nbsp;</td>";
				} else {
					$matrix[] = "<td bgcolor='". $class_bg_color . "' class='" . $class_text_style . "'>" . $cn[$key] . "</td>";
				}
			}
			$matrix[] = "</tr>";
		}
	}
	
	$matrix[] = "</table>";
	return $matrix;
}

//*********************************************************************
// CREATE DOWN AND ACROSS STYLE SCHEDULE WITH STUDIO (Down BY studio2)
//*********************************************************************
function schedule_down_studio_across($schedule_no,$studioAdmin, $getSchedInfo, $row_getSchedInfo)
{

	$table_width = $row_getSchedInfo['max_width'];
	$section_bg_color = $row_getSchedInfo['section_bg_color'];
	$section_text_style = $row_getSchedInfo['section_text_style'];
	$studio_text_style = $row_getSchedInfo['studio_text_style']; 
	$border_color = $row_getSchedInfo['border_color'];
	$class_brdr_color = $row_getSchedInfo['class_brdr_color'];
	$class_bg_color = $row_getSchedInfo['class_bg_color'];
	$class_text_style = $row_getSchedInfo['class_text_style'];
	
	// Select the distinct studio names and does not include a blank studio name in case user forgets to enter one
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$query_getStudios = "SELECT DISTINCT studio FROM schedule WHERE schedule_no='$schedule_no' AND status='A' AND studio != '' ORDER BY studio";
	$getStudios = mysqli_query($studioAdmin,$query_getStudios) or die(db_error_handle());
	$totalRows_getStudios = mysqli_num_rows($getStudios);
	 
	// Create array with studio names
	while($rowstudio = mysqli_fetch_array($getStudios))
	{
		$studios[] = $rowstudio;
	}
	$num_studios = count($studios);
	
	// Selects the distinct days on which there are classes
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$query_getDays = "SELECT day, dayno FROM schedule WHERE schedule_no='$schedule_no' AND status='A' GROUP BY day ORDER BY dayno";
	$getDays = mysqli_query($studioAdmin, $query_getDays) or die(db_error_handle());
	$totalRows_getDays = mysqli_num_rows($getDays);
	
	// Populates array with key=>value pairs from recordset
	$schedInfo = mysqli_fetch_assoc($getSchedInfo);
	
	// Search the schedule speccs array for items to be displayed on schedule
	$keys = array_keys($schedInfo, 'y');
	
	// Get count of fields to be displayed minus one for the Day field
	$keycount = count($keys) + 1;
	
	$matrix = array();
	$matrix[] = "<table width='" . $table_width . "' border='1' bordercolor='" . $border_color . "' align='center' cellpadding='4' cellspacing='0'>";
	
	// Start of main loop - For each day that has classes
	while($rowdays = mysqli_fetch_array($getDays))
	{
		$theday = $rowdays['day'];
		$matrix[] = "<tr bgcolor='" . $section_bg_color . "'>";
		$matrix[] = "<td align='left' class='" . $section_text_style . "' colspan='" . $num_studios . "'>" . $rowdays['day'] . "</td>";
		$matrix[] = "</tr>";
		$matrix[] = "<tr bgcolor='" . $class_bg_color . "'>";
		foreach($studios as $studio)
		{
			$matrix[] = "<td align='center' class='" . $studio_text_style . "'>" . $studio['studio'] . "</td>";
		}
		$matrix[] = "</tr>";
		// Begin looping through time slots
		$halfcount=1;
		$addtime = 30;
		for($timeslot = 700; $timeslot <= 2400; $timeslot += $addtime)
		{
			if($halfcount ==1) {
				$hightime = $timeslot + 30;
			} else {
				$hightime = $timeslot + 70;
			}
			mysqli_select_db($studioAdmin, $database_studioAdmin);
			$query_getSchedule = "SELECT day,class_start_h, class_start_m, class_end_h, class_end_m,am_pm,name,ages,teacher,studio,hour24,dayno,classHL,studio FROM schedule WHERE schedule_no='$schedule_no' AND status='A' AND hour24>='$timeslot' AND hour24< '$hightime' AND day = '$theday' ORDER BY studio";
			$getSchedule = mysqli_query($studioAdmin,$query_getSchedule) or die(db_error_handle());
			$totalRows_getSchedule = mysqli_num_rows($getSchedule);
			
			if($totalRows_getSchedule !=0) 
			{
				$classes = array();
				while($row = mysqli_fetch_assoc($getSchedule))
				{
					$classes[] = $row;
				}
				$studio_classes = array();
				$i = 0;
				foreach($classes as $class)
				{
					$studio_classes[$i] = $class;
					$i += 1;
				}
				$matrix[] = "<tr bgcolor='" . $class_bg_color . "'>";
				$istudio = 0;
				foreach($studios as $studio)
				{
					if($istudio < $num_studios AND $studio_classes[$istudio]['studio'] == $studio['studio'])
					{
						if($studio_classes[$istudio]['classHL'] == 'Bold') 
						{
							$matrix[] = "<td valign='top'><strong>";
						} else {
							$matrix[] = "<td class='" . $class_text_style . "' valign='top'>";
						}
						$matrix[] = $studio_classes[$istudio]['class_start_h'] . ":" . $studio_classes[$istudio]['class_start_m'] . " - " . $studio_classes[$istudio]['class_end_h'] . ":" . $studio_classes[$istudio]['class_end_m'] . "<br>" .$studio_classes[$istudio]['name'];
						if(in_array("ages",$keys)) {
							if($studio_classes[$istudio]['ages'])
							{$matrix[] = "<br>". $studio_classes[$istudio]['ages']; }
						}
						if(in_array("teacher",$keys)) {
							if($studio_classes[$istudio]['teacher'])
								{$matrix[] = "<br>". $studio_classes[$istudio]['teacher'];}
						}
						if($studio_classes[$istudio]['classHL'] == 'Bold') 
						{
							$matrix[] = "</strong></td>";
						} else {
							$matrix[] = "</td>";
						}
						$istudio +=1;
					} else {
						$matrix[] = "<td>&nbsp;</td>";
					}
					
				}
				$matrix[] = "</tr>";
			}
			if($halfcount ==1) {
				$addtime = 30;
				$halfcount = 2;
			} else {
				$addtime = 70;
				$halfcount = 1;
			}
		}
	}
	$matrix[] = "</table>";
	return $matrix;
}
?>