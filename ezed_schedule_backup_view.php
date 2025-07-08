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


$schedule = $_GET['schedule_no'];
$updated = $_GET['updated'];

// Get site information from site table
mysqli_select_db($studioAdmin, $database_studioAdmin);
$query_getSiteInfo = "SELECT css_path FROM site";
$getSiteInfo = mysqli_query($studioAdmin, $query_getSiteInfo);
$row_getSiteInfo = mysqli_fetch_assoc($getSiteInfo);
	
$css_path = $row_getSiteInfo['css_path'];


// Query the sched_info table to get table properties
mysqli_select_db($studioAdmin, $database_studioAdmin);
$query_getSchedInfo = "SELECT * FROM sched_info WHERE schedule_no='$schedule'";
$getSchedInfo = mysqli_query($studioAdmin, $query_getSchedInfo);
while($row_getSchedInfo = mysqli_fetch_assoc($getSchedInfo))
{
	$type = $row_getSchedInfo['sched_type'];
	$studio = $row_getSchedInfo['studio'];
	$reg_page_id = $row_getSchedInfo['reg_page_id'];
	$table_width = $row_getSchedInfo['max_width'];
	$section_bg_color = $row_getSchedInfo['section_bg_color'];
	$section_text_color = $row_getSchedInfo['section_text_color'];
	$studio_text_color = $row_getSchedInfo['studio_text_color'];
}
mysqli_data_seek($getSchedInfo,0);
if($type == "Across") {
	$test = schedule_across($schedule,$updated);
} elseif($type == "Across BY studio") {
	$test = schedule_across_studio($schedule,$updated);
} elseif($type == "Down") {
	$test = schedule_down($schedule,$updated);
} elseif($type == "Down BY studio") {
	$test = schedule_down_studio($schedule,$updated);
} elseif($type == "Class") {
	$test = schedule_classes($schedule);
} elseif($type == "Down BY studio2") {
		$test = schedule_down_studio_across($schedule,$updated);
} else {
	echo "that type of schedule does not exist";
}
foreach($test as $t) {$matrix .= $t;}
?>
<div align="right"><br />
  <input type="button" button style="font: bold 16px Arial" name="Button" value="Close this preview window" onclick="self.close()" />
</div>
<?php
echo $matrix;


//*********************************************************
// Function to create an across type schedule table
//*********************************************************
function schedule_across($schedule_no, $dt_updated)
{
	global $studioAdmin, $css_path, $getSchedInfo, $table_width;
	
	// Selects the distinct days on which there are classes
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$query_getDays = "SELECT day, dayno FROM schedule WHERE schedule_no='$schedule_no' AND status='B' AND updated='$dt_updated' GROUP BY day ORDER BY dayno";
	$getDays = mysqli_query($studioAdmin, $query_getDays);
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
	$matrix[] = "<html><head>";
	$matrix[] = "<link href='" . $css_path . "' rel='stylesheet' type='text/css' />";
	$matrix[] = "</head><body>";
	$matrix[] = "<table width='700' border='1' align='center' cellpadding='8' cellspacing='0'>";
	$matrix[] = "<tr bgcolor='#FFFFFF'>";
	
	// Create cell (column) for each day of the week that has a class in the header row
	foreach($daysofweek as $days)
	{
		$matrix[] = "<td class='subhead'>" . $days . "</td>";
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
		$query_getSchedule = "SELECT day,class_start_h, class_start_m, class_end_h, class_end_m,am_pm,name,ages,teacher,studio,hour24,dayno, classHL FROM schedule WHERE schedule_no='$schedule_no' AND status='b' AND updated='$dt_updated' ORDER BY hour24,dayno";
		$getSchedule = mysqli_query($studioAdmin, $query_getSchedule) or die(db_error_handle());
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
							$matrix[] = "<td valign= 'top'><p class='text'>";
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
										$matrix[] = $row[$key] . "<br>";
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
										$matrix[] = $row[$key] . "<br>";
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
	$matrix[] = "</table>";
	$matrix[] = "</body><html>";
	return $matrix;
}

//*********************************************************
// Fucntion to create a down type schedule table
//*********************************************************
function schedule_down($schedule_no, $dt_updated)
{
	global $studioAdmin, $css_path, $getSchedInfo, $table_width;
	
	// Select all class records from schedule table in the studio database
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$query_getSchedule = "SELECT day,class_start_h, class_start_m, class_end_h, class_end_m,am_pm,name,ages,teacher,studio,hour24,dayno,classHL FROM schedule WHERE schedule_no='$schedule_no' AND status='B' AND updated='$dt_updated' ORDER BY dayno,hour24";
	$getSchedule = mysqli_query($studioAdmin, $query_getSchedule) or die(db_error_handle());
	$totalRows_getSchedule = mysqli_num_rows($getSchedule);
	
	// Selects the distinct days on which there are classes
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$query_getDays = "SELECT day, dayno FROM schedule WHERE schedule_no='$schedule_no' AND status='B' AND updated='$dt_updated' GROUP BY day ORDER BY dayno";
	$getDays = mysqli_query($studioAdmin, $query_getDays);
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
	
	// Get count of fields to be displayed minus one for the Day field
	$keycount = count($keys) + 1;
	
	$matrix = array();
	$matrix[] = "<html><head>";
	$matrix[] = "<link href='" . $css_path . "' rel='stylesheet' type='text/css' />";
	$matrix[] = "</head><body>";
	$matrix[] = "<table width='700' border='1' align='center' cellpadding='8' cellspacing='0'>";
	
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
		$matrix[] = "<tr bgcolor='#FFFFFF'><td colspan=" . $keycount . " class='subhead'>" . $rowdays['day'] . "</td></tr>";
		foreach($day_classes as $dc)
		{
			$matrix[] = "<tr>";
			foreach($keys as $key)
			{
				if($dc[$key] == NULL)
				{
					$matrix[] = "<td>&nbsp;</td>";
				} else {
					if($dc['classHL'] == 'Bold') 
					{ 
						if($key == 'class_start_h') {
							$matrix[] = "<td class='text'><strong>" . $dc[$key] . ":";
						} elseif($key == 'class_start_m') {
							$matrix[] = $dc[$key] . " - ";
						} elseif($key == 'class_end_h') {
							$matrix[] = $dc[$key] . ":";
						} elseif($key == 'class_end_m') {
							$matrix[] = $dc[$key] . "</strong></td>";
						} elseif($dc[$key] != '') {
							$matrix[] = "<td class='text'><strong>" . $dc[$key] . "</strong></td>";
						}
					} else {
						if($key == 'class_start_h') {
							$matrix[] = "<td class='text'>" . $dc[$key] . ":";
						} elseif($key == 'class_start_m') {
							$matrix[] = $dc[$key] . " - ";
						} elseif($key == 'class_end_h') {
							$matrix[] = $dc[$key] . ":";
						} elseif($key == 'class_end_m') {
							$matrix[] = $dc[$key] . "</td>";
						} elseif($dc[$key] != '') {
							$matrix[] = "<td class='text'>" . $dc[$key] . "</td>";
						}
					}
				}
			}
			$matrix[] = "</tr>";
		}
	}
	
	$matrix[] = "</table>";
	$matrix[] = "</body><html>";
	return $matrix;
}

//***************************************************************
// Fucntion to create an across type with studio schedule table
//***************************************************************
function schedule_across_studio($schedule_no, $dt_updated)
{
	global $studioAdmin, $css_path, $getSchedInfo;
	
	// Selects the distinct days on which there are classes
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$query_getDays = "SELECT day, dayno FROM schedule WHERE schedule_no='$schedule_no' AND status='B' AND updated='$dt_updated' GROUP BY day ORDER BY dayno";
	$getDays = mysqli_query($studioAdmin, $query_getDays);
	$totalRows_getDays = mysqli_num_rows($getDays);
	
	// Select the distinct studio names and does not include a blank studio name in case user forgets to enter one
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$query_getStudios = "SELECT DISTINCT studio FROM schedule WHERE schedule_no='$schedule_no' AND status='B' AND updated='$dt_updated' AND studio != '' ORDER BY studio";
	$getStudios = mysqli_query($studioAdmin,$query_getStudios);
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
	$matrix[] = "<html><head>";
	$matrix[] = "<link href='" . $css_path . "' rel='stylesheet' type='text/css' />";
	$matrix[] = "</head><body>";
	foreach($studios as $studio) {
		// Build table and populate first row with day names
		$studioname = $studio['studio'];
		
		$matrix[] = "<table width='700' border='1' align='center' cellpadding='8' cellspacing='0'>";
		$matrix[] = "<tr bgcolor='#FFFFFF'><td class='heading' align='left' colspan=" .$daycount. "><strong><font color='#FFFFFF'>" . $studio['studio'] . "</font></strong></td></tr>";
		$matrix[] = "<tr bgcolor='#FFFFFF'>";
		
		// Create cell (column) for each day of the week that has a class in the header row
		foreach($daysofweek as $days)
		{
			$matrix[] = "<td align='center' class='subhead'>" . $days . "</td>";
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
			$query_getSchedule = "SELECT day,class_start_h, class_start_m, class_end_h, class_end_m,am_pm,name,ages,teacher,studio,hour24,dayno, classHL FROM schedule WHERE schedule_no='$schedule_no' AND status='B' AND hour24>='$timeslot' AND hour24< '$hightime' AND studio='$studioname' AND status='B' AND updated='$dt_updated' ORDER BY studio,dayno, hour24";
			$getSchedule = mysqli_query($studioAdmin, $query_getSchedule) or die(db_error_handle());
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
								$matrix[] = "<td valign= 'top'><p class='text'>";
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
											$matrix[] = $row[$key] . "<br>";
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
											$matrix[] = $row[$key] . "<br>";
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
		$matrix[] = "</table>";
		$matrix[] = "<p>&nbsp;</p>";
	}
	$matrix[] = "</body><html>";
	return $matrix;
}

//***********************************************************
// Fucntion to create a down type with studio schedule table
//***********************************************************
function schedule_down_studio($schedule_no, $dt_updated)
{
	global $studioAdmin, $css_path, $getSchedInfo, $table_width;
	
	// Select all class records from schedule table in the studio database
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$query_getSchedule = "SELECT day,class_start_h, class_start_m, class_end_h, class_end_m,am_pm,name,ages,teacher,studio,hour24,dayno,classHL FROM schedule WHERE schedule_no='$schedule_no' AND status='B' AND updated='$dt_updated' ORDER BY dayno,hour24,studio";
	$getSchedule = mysqli_query($studioAdmin, $query_getSchedule) or die(db_error_handle());
	$totalRows_getSchedule = mysqli_num_rows($getSchedule);
	
	// Selects the distinct days on which there are classes
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$query_getDays = "SELECT day, dayno FROM schedule WHERE schedule_no='$schedule_no' AND status='B' AND updated='$dt_updated' GROUP BY day ORDER BY dayno";
	$getDays = mysqli_query($studioAdmin, $query_getDays);
	$totalRows_getDays = mysqli_num_rows($getDays);
	
		// Select the distinct studio names and does not include a blank studio name in case user forgets to enter one
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$query_getStudios = "SELECT DISTINCT studio FROM schedule WHERE schedule_no='$schedule_no' AND status='B' AND updated='$dt_updated' AND studio != '' ORDER BY studio";
	$getStudios = mysqli_query($studioAdmin,$query_getStudios);
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
	
	// Search the schedule speccs array for items to be displayed on schedule
	$keys = array_keys($schedInfo, 'y');
	array_shift($keys);
	
	// Get count of fields to be displayed minus one for the Day field
	$keycount = count($keys) + 1;
	
	$matrix = array();
	$matrix[] = "<html><head>";
	$matrix[] = "<link href='" . $css_path . "' rel='stylesheet' type='text/css' />";
	$matrix[] = "</head><body>";
	
	for($i = 0; $i < 2; $i++)
	{
		$matrix[] = "<table width='700' border='1' align='center' cellpadding='8' cellspacing='0'>";
	
		// Start of main loop - For each day that has classes
		while($rowdays = mysqli_fetch_array($getDays))
		{
			$temp1 = array();
			$i = 0;
			foreach($schedule as $class)
			{
				if($class['day'] == $rowdays['day'])
				{
					$day_classes[$i] = $class;
					$i += 1;
				}
			}
			$matrix[] = "<tr bgcolor='#FFFFFF'><td align='left' colspan=" . $keycount . " class='subhead'>" . $rowdays['day'] . "</td></tr>";
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
				$matrix[] = "<tr><td rowspan = " . $classcount . " valign='top'><span class='subhead'>" . $studio['studio'] . "</span></td>";
				$i3 = 0;
				foreach($studio_class as $sc)
				{
					if($i3 >= 1)
					{
						$matrix[] = "<tr>";
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
									$matrix[] = "<td class='text'><strong>" . $sc[$key] . ":";
								} elseif($key == 'class_start_m') {
									$matrix[] = $sc[$key] . " - ";
								} elseif($key == 'class_end_h') {
									$matrix[] = $sc[$key] . ":";
								} elseif($key == 'class_end_m') {
									$matrix[] = $sc[$key] . "</strong></td>";
								} elseif($sc[$key] != '') {
									$matrix[] = "<td class='text'><strong>" . $sc[$key] . "</strong></td>";
								}
							} else {
								if($key == 'class_start_h') {
									$matrix[] = "<td class='text'>" . $sc[$key] . ":";
								} elseif($key == 'class_start_m') {
									$matrix[] = $sc[$key] . " - ";
								} elseif($key == 'class_end_h') {
									$matrix[] = $sc[$key] . ":";
								} elseif($key == 'class_end_m') {
									$matrix[] = $sc[$key] . "</td>";
								} elseif($sc[$key] != '') {
									$matrix[] = "<td class='text'>" . $sc[$key] . "</td>";
								}
							}
						}
					}
					$matrix[] = "</tr>";
					$i3 += 1;
				}
			}
		}
		$matrix[] = "</table>";
	}
	$matrix[] = "</body><html>";
	return $matrix;
}
//*********************************************************
// Fucntion to create a classes type schedule table
//*********************************************************
function schedule_classes($schedule_no, $dt_updated)
{
	if(!function_exists(array_insert)) {
		function array_insert(&$array, $offset, $new)
		{
			array_splice($array, $offset, 0, $new);
		}
	}
	global $studioAdmin, $css_path, $getSchedInfo;
	
	// Select all class records from schedule table in the studio database
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$query_getSchedule = "SELECT day,class_start_h, class_start_m, class_end_h, class_end_m,am_pm,name,ages,teacher,studio,hour24,dayno,classHL FROM schedule WHERE schedule_no='$schedule_no' AND status='B' AND updated='$dt_updated' ORDER BY dayno,hour24";
	$getSchedule = mysqli_query($studioAdmin, $query_getSchedule) or die(db_error_handle());
	$totalRows_getSchedule = mysqli_num_rows($getSchedule);
	
	// Selects the distinct classes names
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$query_getNames = "SELECT DISTINCT name FROM schedule WHERE schedule_no='$schedule_no' AND status='B' AND updated='$dt_updated'";
	$getNames = mysqli_query($studioAdmin, $query_getNames) or die(db_error_handle());
	$totalRows_getDNames = mysqli_num_rows($getNames);
	
	// Selects the distinct days on which there are classes
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$query_getDays = "SELECT day, dayno FROM schedule WHERE schedule_no='$schedule_no' AND status='B' AND updated='$dt_updated' GROUP BY day ORDER BY dayno";
	$getDays = mysqli_query($studioAdmin, $query_getDays);
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
	$matrix[] = "<html><head>";
	$matrix[] = "<link href='" . $css_path . "' rel='stylesheet' type='text/css' />";
	$matrix[] = "</head><body>";
	$matrix[] = "<table width='700' border='1' align='center' cellpadding='8' cellspacing='0'>";
	
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
		$matrix[] = "<tr bgcolor='#FFFFFF'><td colspan=" . $keycount . " class='subhead'>" . $rownames['name'] . "</td></tr>";
		foreach($class_names as $cn)
		{
			$matrix[] = "<tr>";
			foreach($keys as $key)
			{
				if($cn[$key] == NULL)
				{
					$matrix[] = "<td>&nbsp;</td>";
				} else {
					$matrix[] = "<td class='text'>" . $cn[$key] . "</td>";
				}
			}
			$matrix[] = "</tr>";
		}
	}
	
	$matrix[] = "</table>";
	$matrix[] = "</body><html>";
	return $matrix;
}
//*********************************************************************
// CREATE DOWN AND ACROSS STYLE SCHEDULE WITH STUDIO (Down BY studio2)
//*********************************************************************
function schedule_down_studio_across($schedule_no, $dt_updated)
{
	global $studioAdmin, $css_path, $getSchedInfo, $table_width, $studio_text_color;
	
	// Select the distinct studio names and does not include a blank studio name in case user forgets to enter one
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$query_getStudios = "SELECT DISTINCT studio FROM schedule WHERE schedule_no='$schedule_no' AND status='B' AND updated='$dt_updated' AND studio != '' ORDER BY studio";
	$getStudios = mysqli_query($studioAdmin,$query_getStudios);
	$totalRows_getStudios = mysqli_num_rows($getStudios);
	 
	// Create array with studio names
	while($rowstudio = mysqli_fetch_array($getStudios))
	{
		$studios[] = $rowstudio;
	}
	$num_studios = count($studios);
	
	// Selects the distinct days on which there are classes
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$query_getDays = "SELECT day, dayno FROM schedule WHERE schedule_no='$schedule_no' AND status='B' AND updated='$dt_updated' GROUP BY day ORDER BY dayno";
	$getDays = mysqli_query($studioAdmin, $query_getDays);
	$totalRows_getDays = mysqli_num_rows($getDays);
	
	// Populates array with key=>value pairs from recordset
	$schedInfo = mysqli_fetch_assoc($getSchedInfo);
	
	// Search the schedule speccs array for items to be displayed on schedule
	$keys = array_keys($schedInfo, 'y');
	
	// Get count of fields to be displayed minus one for the Day field
	$keycount = count($keys) + 1;
	
	$matrix = array();
	$matrix[] = "<html><head>";
	$matrix[] = "<link href='" . $css_path . "' rel='stylesheet' type='text/css' />";
	$matrix[] = "</head><body>";
	$matrix[] = "<table width='700' border='1' align='center' cellpadding='4' cellspacing='0'>";
	
	// Start of main loop - For each day that has classes
	while($rowdays = mysqli_fetch_array($getDays))
	{
		$theday = $rowdays['day'];
		$matrix[] = "<tr bgcolor='#FFFFFF'>";
		$matrix[] = "<td align='left' class='subhead' colspan='" . $num_studios . "'>" . $rowdays['day'] . "</td>";
		$matrix[] = "</tr>";
		$matrix[] = "<tr bgcolor='#FFFFFF'>";
		foreach($studios as $studio)
		{
			$matrix[] = "<td align='center' class='subhead'><font color='" . $studio_text_color . "'>" . $studio['studio'] . "</font></td>";
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
			$getSchedule = mysqli_query($studioAdmin, $query_getSchedule) or die(db_error_handle());
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
				$matrix[] = "<tr>";
				$istudio = 0;
				foreach($studios as $studio)
				{
					if($istudio < $num_studios AND $studio_classes[$istudio]['studio'] == $studio['studio'])
					{
						if($studio_classes[$istudio]['classHL'] == 'Bold') 
						{
							$matrix[] = "<td class='textbold' valign='top'>";
						} else {
							$matrix[] = "<td class='text' valign='top'>";
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
						$matrix[] = "</td>";
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
	$matrix[] = "</body></html>";
	return $matrix;
}
?>