<?php require_once('../../Connections/studioAdmin_i.php'); ?>
<?php
//initialize the session
if (!isset($_SESSION)) {
  session_start();
}

function db_error_handle()
{
	mail('admin@studioofdancehosting.com', 'database connection error', 'An error occured in the index.php file');
	header("Location: db_error.htm");
}
$schedule = $_GET['schedule_no'];
mysqli_select_db($studioAdmin, $database_studioAdmin);
$query_addClasses = "SELECT * FROM schedule WHERE schedule_no = '$schedule'";
$addClasses = mysqli_query($studioAdmin, $query_addClasses);
$row_addClasses = mysqli_fetch_assoc($addClasses);
$totalRows_addClasses = mysqli_num_rows($addClasses);

if((isset($_POST['Cancel'])) && ($_POST['Cancel'] == "Cancel and return to Schedule page")) {
	header("Location: ezed_schededit.php?schedule_no=$schedule");
	exit;
}

$firstTime = !array_key_exists('name', $_POST);
if(!$firstTime)
//if(array_key_exists('name', $_POST))
{	
	$size = count($_POST['day']);
	$i = 0;
	$icount = 0;
	$err_name = array();
	$err_start = array();
	$studios = array();
	$err_studio = 0;
	// Loop through all arrays from form and check for empty start or name fields
	while($i <= $size - 1)
	{
		$day = $_POST['day'];
		if(empty($_POST['class_start_h'])) { // if no start time - set error flag
			$err_start[] = $i; 
			$start_h = "";
			$start_m = "";
		} else {
			$start_h = $_POST['class_start_h'];
			$start_m = $_POST['class_start_m'];
		}
		$end_h = $_POST['class_end_h']; // Not necessary to check end time of class
		$end_m = $_POST['class_end_m']; 
		$ampm = $_POST['am_pm'];
		$name = trim($_POST['name']);
		if(empty($name)) {
			$err_name[] = $i; 
			$name = "";
		} else {
			$name = $_POST['name'];
		}
		$ages = $_POST['ages'];
		$teacher = $_POST['teacher'];
		$studio = trim($_POST['studio']);
			if(!in_array($studio, $studios)) {
				$studios[] = $studio;
			}

		// Check for any records with Remove in the day field and do not add them
		if($day != 'Remove')
		{
			$iday = $day;
			$istart_h = $start_h;
			$istart_m = $start_m;
			$iend_h = $end_h;
			$iend_m = $end_m;
			$iampm = $ampm;
			$iname = $name;
			$iages = $ages;
			$iteacher = $teacher;
			$istudio = $studio;
			if($iampm == "AM") { 
				$hour24 = $start_h . $start_m;
			} else { 
				if($start_h == 12) // process to convert afternoon hours to 24 clock
					{
						$hour24 = $start_h . $start_m; 
					} else {
						$start_h24 = $start_h + 12;
						$hour24 = $start_h24 . $start_m;
					}
			}
			switch($day) {
				case "Monday":
					$dayno = 1;
					break;
				case "Tuesday":
					$dayno = 2;
					break;
				case "Wednesday":
					$dayno = 3;
					break;
				case "Thursday":
					$dayno = 4;
					break;
				case "Friday":
					$dayno = 5;
					break;
				case "Saturday":
					$dayno = 6;
					break;
				case "Sunday":
					$dayno = 7;
					break;
			}
			$icount++;
		}
		$i++;
	}
	$a_count = count($studios);
	if(in_array('', $studios) and ($a_count > 1)){
		$err_studio = $a_count;
	}
}
// If there are no errors and the form has been submitted - save to table. Otherwise redisplay form
if(!$firstTime && !$err_name && !$err_start && !$err_studio)
{
	// If all fieldds are filled out - save information to schedule table
	$i = 0;
	while($i <= $icount -1)
		{
		$results = mysqli_query("INSERT INTO schedule (schedule_no, status, day, class_start_h, class_start_m, class_end_h, class_end_m, am_pm, name, ages, teacher, studio, hour24, dayno) VALUES ('$schedule', 'A', '$day','$start_h', '$start_m', '$end_h', '$end_m', '$ampm', '$name', '$ages', '$teacher', '$studio','$hour24','$dayno')");
		$i++;
		}
		/*if($results) {
			echo "the query was successful";
		} else {
			echo "the query failed";
		}*/
	header("Location: ezed_schededit.php?schedule_no=$schedule");
} else {
	?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>Add Class Page</title>
	<link href="css/edit1.css" rel="stylesheet" type="text/css" />
	</head>
	
<body bgcolor="443B34">
<div align="center">
<table width="800" border="1" cellpadding="8" cellspacing="0" bordercolor="#000000" bgcolor="#FFFFFF">

    <tr> <td>
	<table width="790" border="1" cellpadding="4" cellspacing="0" bordercolor="#999999">
            <tr bgcolor="#CCCCCC">
              <td class="style5">
			 	 <table width="100%">
				 	<tr>
      					<td bgcolor="cccccc"><span class="subhead">Add Class Page </span></td>
              			<td align="right" bgcolor="#CCCCCC"><button class="buttons" title="Changes since your last save will be lost" onClick="location.href='ezed_admin.php'">Return to administration menu</button>&nbsp;&nbsp;&nbsp;<button class="buttons" onClick="window.location='<?php echo $logoutAction ?>'">Logout</button>
						</td>
					</tr>
				</table>
			</td></tr>
			<tr><td>
			 <form id="schededit" name="schededit" method="post" action="<?php $_SERVER['../PHP_SELF'];?>">
			   <table width="100%" cellpadding="2" cellspacing="0" bgcolor="#CCCCCC">
		<tr>
		  <th class="text" scope="col"><strong>Day</strong></th>
		  <th class="text" scope="col"><strong>From</strong></th>
		  <th class="text" scope="col"><strong>To</strong></th>
		  <th class="text" scope="col"><strong>AM/PM</strong></th>
		  <th class="text" scope="col"><strong>Name</strong></th>
		  <th class="text" scope="col"><strong>Ages</strong></th>
		  <th class="text" scope="col"><strong>Teacher</strong></th>
		  <th class="text" scope="col"><strong>Studio</strong></th>
		  <th class="text" scope="col"><strong>Class<br>Highlight</strong></th>
		</tr>
		<?php 		
		$addnum = 1;
		$i = 1;
		while($i <= $addnum) { 
			$day = $iday;
			$start_h = $istart_h;
			$start_m = $istart_m;
			$end_h = $iend_h;
			$end_m = $iend_m;
			$ampm = $iampm;
			$name = $iname;
			$ages = $iages;
			$teacher = $iteacher;
			$studio = $istudio;
		?>
		  <tr>
			<td><select name="day">
              <option value="<?php echo $day; ?>"selected><?php echo $day; ?></option>
              <option value="Monday">Monday</option>
              <option value="Tuesday">Tuesday</option>
              <option value="Wednesday">Wednesday</option>
              <option value="Thursday">Thursday</option>
              <option value="Friday">Friday</option>
              <option value="Saturday">Saturday</option>
              <option value="Sunday">Sunday</option>
              <option value="Remove">Remove</option>
            </select></td>
			<td><span class="subhead">
			  <input name="class_start_h" type="text" id="class_start_h" value="<?php echo $start_h; ?>" size="1" maxlength="2" class="time"/> 
			  :</span>			  
			    <input name="class_start_m" type="text" id="class_start_m" value="<?php echo $start_m; ?>" size="1" maxlength="2" class="time"/></td>
						<td><input name="class_end_h"  type="text" id="class_end_h" value="<?php echo $end_h; ?>" size="1" maxlength="2" class="time"/>
						  <span class="subhead">:				          </span>
					    <input name="class_end_m"  type="text" id="class_end_m" value="<?php echo $end_m; ?>" size="1" maxlength="2" class="time"/></td>
		            <td><select name="am_pm">
                      <option value="AM">AM</option>
                      <option value="PM">PM</option>
                    </select></td>
			<td><input name="name" id="name" type="text" value="<?php echo $name; ?>" /></td>
			<td><div align="center">
			  <input name="ages" type="text" id="ages" value="<?php echo $ages; ?>" size="10" />
			</div></td>
			<td><input name="teacher" id="teacher"type="text" value="<?php echo $teacher; ?>" size="10" /></td>
			<td colspan="2"><input name="studio" type="text" id="studio" value="<?php echo $highlight; ?>" size="10" maxlength="10" /></td>
			<td><select name="highlight">
				<option value="Normal">Normal</option>
				<option value="Bold">Bold</option>
				</select>
			</td>
			</tr>
		  <?php
		$i++; } ?>
	  </table>
  <p align="right">
    <div align="left">
      <p class="text">Once you are done entering the information, click the Add Class button. This will add the class to your schedule and return you to the schedule edit page. <br>
        <strong>Note:</strong>        To update your webpage with this new class, you need to also click the &quot;Save changes and upload&quot; button on the schedule edit page. </p>
      <p>
  <input name="Submit" type="submit" class="buttons" value="Add class" /></p>
      <p>
        <input name="Cancel" type="submit" class="buttons" id="Cancel" value="Cancel and return to Schedule page" />
        </p>
    </div>
		
	  </p>
</form></td></tr></table>
	</td></tr></table>
	<p>&nbsp;</p>
	</div>
	</body>
</html>
<?php
}
mysqli_free_result($addClasses);
?>