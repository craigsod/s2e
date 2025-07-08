<?php require_once('../../Connections/studioAdmin_i.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}

$_SESSION['ReturnTo'] = $_SERVER['PHP_SELF'] . '?schedule_no=' . $_GET['schedule_no'] . '&page_id=' . $_GET['page_id'];

$MM_authorizedUsers = "3,1";
$MM_donotCheckaccess = "false";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && false) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "index.php";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($QUERY_STRING) && strlen($QUERY_STRING) > 0) 
  $MM_referrer .= "?" . $QUERY_STRING;
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}

function db_error_handle()
{
	mail('admin@studioofdancehosting.com', 'database connection error', 'An error occured in the index.php file');
	header("Location: db_error.htm");
}

$schedule = $_GET['schedule_no'];
$page_id = $_GET['page_id'];


if((isset($_POST['Cancel'])) && ($_POST['Cancel'] == "Return to Admin menu")) {
	header('Location: ezed_admin.php');
	exit;
}

if((isset($_POST['add'])) && ($_POST['add'] == "Add a class")) {
	header("Location: ezed_add_class.php?schedule_no=$schedule");
	exit;
}


$firstTime = !array_key_exists('name', $_POST);
if(!$firstTime)
{	
	$size = $_POST['num_classes'];
	//$size = count($_POST['name']); // How many days in from schedule builder form
	$i = 0;
	$icount = 0;
	$err_name = array();
	$err_start = array();
	$studios = array();
	$err_studio = 0;
	$err_studio_blank = 0;
	// Loop through all arrays from form and check for empty start or name fields
	while($i <= $size - 1)
	{
		$day = $_POST['day'][$i];
		if(empty($_POST['class_start_h'][$i])) { // if no start time - set error flag
			$err_start[] = $i; 
			$start_h = "";
			$start_m = "";
		} else {
			$start_h = $_POST['class_start_h'][$i];
			$start_m = $_POST['class_start_m'][$i];
		}
		$end_h = $_POST['class_end_h'][$i]; // Not necessary to check end time of class
		$end_m = $_POST['class_end_m'][$i]; 
		$ampm = $_POST['am_pm'][$i];
		if(empty($_POST['name'][$i])) { // If no class name - set error flag
			$err_name[] = $i; 
			$name = "";
		} else {
			$name = $_POST['name'][$i];
		}
		$ages = $_POST['ages'][$i];
		$teacher = $_POST['teacher'][$i];
		$studio = strtoupper($_POST['studio'][$i]); // Set var to check studios later
			if(!in_array($studio, $studios)) {
				$studios[] = $studio;
			}
		$highlight = $_POST['highlight'][$i];
		
		// Start building schedule array (i[name] arrays indexed by $icount counter)
		if($day != 'Remove')  // Do this if the DAY variable does NOT equal Remove
		{
			$iday[$icount] = $day;
			$istart_h[$icount] = $start_h;
			$istart_m[$icount] = $start_m;
			$iend_h[$icount] = $end_h;
			$iend_m[$icount] = $end_m;
			$iampm[$icount] = $ampm;
			$iname[$icount] = $name;
			$iages[$icount] = $ages;
			$iteacher[$icount] = $teacher;
			$istudio[$icount] = $studio;
			$ihighlight[$icount] = $highlight;
			if($iampm[$icount] == "AM") { 
				$ihour24[$icount] = $start_h . $start_m;
			} else { 
				if($start_h == 12) // process to convert afternoon hours to 24 clock
					{
						$ihour24[$icount] = $start_h . $start_m; 
					} else {
						$start_h = $start_h + 12;
						$ihour24[$icount] = $start_h . $start_m;
					}
			}
			switch($iday[$icount]) { // process to convert day name to day number
				case "Monday":
					$idayno[$icount] = 1;
					break;
				case "Tuesday":
					$idayno[$icount] = 2;
					break;
				case "Wednesday":
					$idayno[$icount] = 3;
					break;
				case "Thursday":
					$idayno[$icount] = 4;
					break;
				case "Friday":
					$idayno[$icount] = 5;
					break;
				case "Saturday":
					$idayno[$icount] = 6;
					break;
				case "Sunday":
					$idayno[$icount] = 7;
					break;
			}
			$icount++;
		}
		$i++;
	}
}

// If there are no errors and the form has been submitted - save to table. Otherwise redisplay form
if(!$firstTime && !$err_name && !$err_start && !$err_studio && !$err_studio_blank)
{
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$i = 0;
	while($i <= $icount -1)
		{
		mysqli_query("INSERT INTO schedule (schedule_no, status, day, class_start_h, class_start_m, class_end_h, class_end_m, am_pm, name, ages, teacher, studio, hour24, dayno, classHL) VALUES ('$schedule','A','$iday[$i]','$istart_h[$i]', '$istart_m[$i]', '$iend_h[$i]', '$iend_m[$i]', '$iampm[$i]', '$iname[$i]', '$iages[$i]', '$iteacher[$i]', '$istudio[$i]','$ihour24[$i]','$idayno[$i]','$ihighlight[$i]')");
		$i++;
		}
	header("Location: ezed_add_schedule.php?schedule_no=$schedule&page_id=$page_id");
} else {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Schedule Edit Page</title>
<link href="css/edit1.css" rel="stylesheet" type="text/css" />
</head>
<body bgcolor="443B34">
<div align="center">
<table width="800" border="1" cellpadding="4" cellspacing="0" bordercolor="#000000" bgcolor="#FFFFFF">

    <tr> <td valign="top" bordercolor="FFFFFF">
	<table width="790" border="1" cellpadding="4" cellspacing="0" bordercolor="#999999">
            <tr bgcolor="#CCCCCC">
              <td class="style5">
			 	 <table width="100%">
				 	<tr>
      					<td bgcolor="cccccc"><span class="subhead">Schedule Edit Page </span><span class="style15"><strong></strong></span>
						  <br>
					    <span class="radioLabel">Currently editing schedule: <?php echo $schedule; ?> </span></td>
           			  <td align="right" bgcolor="#CCCCCC"><button class="buttons" title="Changes since your last save will be lost" onClick="location.href='ezed_admin.php'">Return to administration menu</button>&nbsp;&nbsp;&nbsp;<button class="buttons" onClick="window.location='<?php echo $logoutAction ?>'">Logout</button>
						</td>
					</tr>
				</table>
			</td></tr>
			<tr><td>
			 <form id="schededit" name="schededit" method="post" action="<?php $_SERVER['../PHP_SELF'];?>">
			    <table width="95%" border="0" cellspacing="0" cellpadding="8">
			<tr>
			  <td>
			    <input name="Save2" type="submit" class="buttons" value="Save changes and upload" />			    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
			</tr>
  </table>
	  <p class="text">How many classes will be entered? (Max of 10) 

	    <input name="num_classes" type="text" size="5">

	    <br />
	    <span class="warning">
	    <?php
		if($err_start || $err_name)
		{
			echo "There are errors in the information you submitted. Please correct the items below marked with asteriks<br>";
		}
		if($err_studio) echo "You have some blank values in your studio column and when using a 'BY studio' schedule style, all classes <br>must have a studio value.<br>Please fill in all Studio values or make them all blank"; 
		if($err_studio_blank) echo "You have the 'BY studio' option selected but there are no studio name values.<br>Either choose another schedule style or add studio name values to all your classes.";
		?>
	      </span> </p>
	  <table width="100%" cellpadding="" cellspacing="1" bgcolor="#CCCCCC">
		<tr>
		  <th width="104" class="text" scope="col"><strong>Day</strong></th>
		  <th width="108" class="text" scope="col"><div align="center"><strong>From</strong></div></th>
		  <th width="108" class="text" scope="col"><div align="center"><strong>To</strong></div></th>
		  <th width="57" class="text" scope="col"><strong>AM/PM</strong></th>
		  <th width="122" class="text" scope="col"><strong>Name</strong></th>
		  <th width="73" class="text" scope="col"><strong>Grades/Ages</strong></th>
		  <th width="50" class="text" scope="col"><strong>Teacher</strong></th>
		  <th width="50" class="text" scope="col"><strong>Studio</strong></th>
		  <th width="83" class="text" scope="col"><strong>Class<br>
		    Highlight</strong></th>
		</tr>
		<?php 
		// Change this size to determine  how many class slots will be available
		$size = 10;
		
		// create counter to work as array index on text input boxes below 
		$i = 0; 
		while($i <= $size -1) { 
			$day = $iday[$i];
			$start_h = $istart_h[$i];
			$start_m = $istart_m[$i];
			$end_h = $iend_h[$i];
			$end_m = $iend_m[$i];
			$ampm = $iampm[$i];
			$name = $iname[$i];
			$ages = $iages[$i];
			$teacher = $iteacher[$i];
			$studio = $istudio[$i];
			$highlight = $ihighlight[$i];
			
		?>
		  <tr>
			<td><select name="day[<?php $i ?>]">
			<option value=""SELECTED></option>
			<option value="Monday">Monday</option>
			<option value="Tuesday">Tuesday</option>
			<option value="Wednesday">Wednesday</option>
			<option value="Thursday">Thursday</option>
			<option value="Friday">Friday</option>
			<option value="Saturday">Saturday</option>
			<option value="Sunday">Sunday</option>
			<option value="Remove">Remove</option>
			</select>        </td>
			<td><input name="class_start_h[<?php $i ?>]" type="text" id="class_start_h" value="" maxlength="2" class="time"/>
			  <strong class="subhead">:</strong>
			  <input name="class_start_m[<?php $i ?>]" type="text" id="class_start_m[<?php $i ?>]" value="" size="1" maxlength="2" class="time" /></td>
			<td><input name="class_end_h[<?php $i ?>]"  type="text" id="class_end_h" value="" size="1" maxlength="2" class="time" />
			  <strong class="subhead">:</strong>			  
			  <input name="class_end_m[<?php $i ?>]"  type="text" id="class_end_m[<?php $i ?>]" value="" size="1" maxlength="2" class="time" /></td>
			<td><div align="center">
				  <select name="am_pm[<?php $i ?>]">
				  <option value="PM"SELECTED>PM</option>
				  <option value="AM">AM</option>
				  <option value="PM">PM</option>
				  </select>
				</div></td>
			<td><input name="name[<?php $i ?>]" type="text" id="name" value="" size="20" /></td>
			<td><input name="ages[<?php $i ?>]" type="text" id="ages" value="" size="10" /></td>
			<td><input name="teacher[<?php $i ?>]" id="teacher"type="text" value="" size="10" /></td>
			<td><input name="studio[<?php $i ?>]" id="studio" type="text" value="" size="10" /></td>
		    <td><select name="highlight[<?php $i ?>]">
				<option value="Normal"SELECTED>Normal</option>
				<option value="Normal">Normal</option>
				<option value="Bold">Bold</option>
				</select></td>
		  </tr>
		  <?php
			//$row_getSchedule = mysqli_fetch_assoc($getSchedule);
		$i++; 
		}  ?>
  </table>
  <p align="center">
	    
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="Save" type="submit" class="subhead" value="Save changes and upload" />
  </p>
	</form></td></tr></table>
  </td></tr></table>
	<p>&nbsp;</p>
</div>
	</body>
	</html>
<?php
}
mysqli_free_result($getSchedule);
mysqli_free_result($updateSchedule);
mysqli_free_result($getSchedInfo);
?>