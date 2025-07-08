<?php require_once('../../Connections/studioAdmin_i.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}

$_SESSION['ReturnTo'] = $_SERVER['PHP_SELF'] . '?schedule_no=' . $_GET['schedule_no'];

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

if(isset($_GET['newclasses'])) {
	$newclasses = $_GET['newclasses'];
}

// Establish connection
mysqli_select_db($studioAdmin, $database_studioAdmin);

//*********************************************************************
//  GET ALL THE PAGES TO POPULATE THE SIDEBAR MENU
//********************************************************************
$query_getSidePages = "SELECT * FROM pages WHERE editable = 'Y' ORDER BY page_id";
$getSidePages = mysqli_query($studioAdmin, $query_getSidePages);

$schedule = $_GET['schedule_no'];
$page_id = $_GET['page_id'];

$query_getSchedule = "SELECT * FROM schedule WHERE schedule_no = '$schedule' AND status = 'A' ORDER BY dayno, hour24";
$getSchedule = mysqli_query($studioAdmin, $query_getSchedule) or die(db_error_handle());
$row_getSchedule = mysqli_fetch_assoc($getSchedule);
$totalRows_getSchedule = mysqli_num_rows($getSchedule);

mysqli_select_db($studioAdmin, $database_studioAdmin);
$query_updateSchedule = "SELECT * FROM schedule";
$updateSchedule = mysqli_query($studioAdmin, $query_updateSchedule) or die(db_error_handle());
$row_updateSchedule = mysqli_fetch_assoc($updateSchedule);
$totalRows_updateSchedule = mysqli_num_rows($updateSchedule);

mysqli_select_db($studioAdmin, $database_studioAdmin);
$query_getSchedInfo = "SELECT * FROM sched_info WHERE schedule_no = '$schedule'";
$getSchedInfo = mysqli_query($studioAdmin, $query_getSchedInfo);
$row_getSchedInfo = mysqli_fetch_assoc($getSchedInfo);
$totalRows_getSchedInfo = mysqli_num_rows($getSchedInfo);

// Get site information from site table to determine which menu items are available
$query_getSiteInfo = "SELECT * FROM site";
$getSiteInfo = mysqli_query($studioAdmin, $query_getSiteInfo) or die(db_error_handle());
$row_getSiteInfo = mysqli_fetch_assoc($getSiteInfo);

$placecodes = strtoupper($row_getSiteInfo['placecodes']);
$page_tag = strtoupper($row_getSiteInfo['pagetag']);
$page_script = strtoupper($row_getSiteInfo['pagescript']);
$my_account = strtoupper($row_getSiteInfo['myaccount']);

// ********************************************************************
// Query gallery_info table to get all galleries for this site
//
$query_gallery = "SELECT * FROM gallery_info";
$get_galleries = mysqli_query($studioAdmin, $query_gallery);
$num_galleries = mysqli_num_rows($get_galleries);

// ********************************************************************
// CHECK FOR ANY SCHEDULES ON THIS SITE
//
$query_getSchedules = "SELECT * FROM page_schedule GROUP BY schedule_no";
$getSchedules = mysqli_query($studioAdmin, $query_getSchedules);
$totalSchedules = mysqli_num_rows($getSchedules);


if((isset($_POST['Cancel'])) && ($_POST['Cancel'] == "Return to Admin menu")) {
	header('Location: ezed_admin.php');
	exit;
}

if((isset($_POST['add'])) && ($_POST['add'] == "Add classes")) {
	header("Location: ezed_add_classes.php?schedule_no=$schedule");
	exit;
}


$firstTime = !array_key_exists('name', $_POST);
if(!$firstTime)
{	
	$size = count($_POST['day']); // How many days in from schedule builder form
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
		$class_status = $_POST['class_status'][$i];
		
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
			$iclass_status[$icount] = $class_status;
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
	// Get number of elements in array - how many classes with studios - and then check to see if any are blank
	// If there is more than one element in the array and also blank elements - then send error message to user
	// to fill in all studio fields - UNLESS they are not going to display the studio on the schedule page.
	$a_count = count($studios);
	//Check if any of the schedule types are BY studio
	if($_POST['stype'] == 'Across BY studio' || $_POST['stype'] == 'Down BY studio' || $_POST['stype'] == 'Down BY studio2') {$bystudio = TRUE;} else {$bystudio = FALSE;}
	// If there all studio columns are blank but the display studios checkbox is checked - issure error message
	if($a_count == 1 and $bystudio) {
		$err_studio_blank = 1;
	}
	if(in_array('', $studios) and ($a_count > 1) and ($bystudio)){
		$err_studio = $a_count;
	}
}
// If there are no errors and the form has been submitted - save to table. Otherwise redisplay form
if(!$firstTime && !$err_name && !$err_start && !$err_studio && !$err_studio_blank)
{
	//Check if there are 10 saved backup schedules in database
	//If there are - delete the oldest one before UPDATing the current one to a backup 
	//
	// Get updated date from schedule table
	$recno = $_GET['schedule_no'];
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$queryCountSaves = "SELECT DISTINCT updated FROM schedule WHERE schedule_no='$recno' AND status='B'";
	$getCountSaves = mysqli_query($studioAdmin, $queryCountSaves) or die(db_error_handle());
	
	$totalRows_getCountSaves = mysqli_num_rows($getCountSaves);
	//echo "Total saves: " . $totalRows_getCountSaves;
	
	// If there are more than 10 saved backups of the schedule - delete the oldest version
	if($totalRows_getCountSaves > 9)
	{
		$query_getScheduleDate = "SELECT * FROM schedule WHERE schedule_no='$recno' AND status='B' ORDER BY updated ASC LIMIT 1";
		$getScheduleDate = mysqli_query($studioAdmin, $query_getScheduleDate) or die(db_error_handle());
		
		$row_getScheduleDate = mysqli_fetch_assoc($getScheduleDate);
		$oldest = $row_getScheduleDate['updated'];
		
		mysqli_data_seek($getScheduleDate,0);
		mysqli_select_db($studioAdmin, $database_studioAdmin);
		$delete_oldestDate = "DELETE FROM schedule WHERE updated='$oldest' AND status='B'";
		$deleteOldest = mysqli_query($studioAdmin, $delete_oldestDate) or die(db_error_handle());
	}
	// End of check and delete records process
	
	// If all fields are filled out - save current schedule as backup version
	$updateSQL = "UPDATE schedule SET status='B' WHERE schedule_no='$schedule' AND status = 'A'";
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$UpdateSave = mysqli_query($studioAdmin, $updateSQL) or die(db_error_handle());
	
	$i = 0;
	while($i <= $icount -1)
		{
		mysqli_query($studioAdmin,"INSERT INTO schedule (schedule_no, status, day, class_start_h, class_start_m, class_end_h, class_end_m, am_pm, name, ages, teacher, studio, hour24, dayno, class_status, classHL) VALUES ('$schedule','A','$iday[$i]','$istart_h[$i]', '$istart_m[$i]', '$iend_h[$i]', '$iend_m[$i]', '$iampm[$i]', '$iname[$i]', '$iages[$i]', '$iteacher[$i]', '$istudio[$i]','$ihour24[$i]','$idayno[$i]','$iclass_status[$i]','$ihighlight[$i]')");
		$i++;
		}
		
		if($_POST['checkages'] == 'on') { $ages = 'y';} else {$ages = 'n';}
		if($_POST['checkteacher'] == 'on') { $teacher = 'y';} else {$teacher = 'n';}
		if($_POST['checkstudio'] == 'on') { $studio = 'y';} else {$studio = 'n';}
		$stype = $_POST['stype'];
		mysqli_query($studioAdmin,"UPDATE sched_info SET sched_type='$stype', ages='$ages', teacher='$teacher', studio='$studio' WHERE schedule_no='$schedule'");


	$_SESSION['type'] = $_POST['stype'];
	$_SESSION['studio'] = $studio;
	
	header("Location: ezed_schedule_page_builder.php?schedule_no=$schedule");
} else {
?><!DOCTYPE html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8" />
<title><?php echo $_SESSION['site_name']; ?></title>


<style type="text/css">
<!--
.widebox {
  width: 500px;
  }
   .sidenavmain {
	border-left-width:1px; 
	border-left-color:#CCCCCC; 
	border-left-style:solid;
	border-bottom-width:1px; 
	border-bottom-color:#CCCCCC; 
	border-bottom-style:solid;
}
 .sidenav {
	border-left-width:1px; 
	border-left-color:#CCCCCC; 
	border-left-style:solid;
	border-bottom-width:1px; 
	border-bottom-color:#CCCCCC; 
	border-bottom-style:solid;
	border-right-width:1px; 
	border-right-color:#CCCCCC; 
	border-right-style:solid;
}
.sidenavtext {
	font-family: "Franklin Gothic Medium Cond", Verdana, "Arial Narrow";
	font-weight: bold;
	color: #666666;
	font-size: 12px;
	text-decoration:none;
}
.sidenavtext a:link {
	font-family: "Franklin Gothic Medium Cond", Verdana, "Arial Narrow";
	font-weight: bold;
	color: #666666;
	font-size: 12px;
	text-decoration:none;
}
.sidenavtext a:visited {
	font-family: "Franklin Gothic Medium Cond", Verdana, "Arial Narrow";
	font-weight: bold;
	color: #666666;
	font-size: 12px;
	text-decoration:none;
}

.sidenavtext a:hover {
	font-family: "Franklin Gothic Medium Cond", Verdana, "Arial Narrow";
	font-weight: bold;
	color: #666666;
	font-size: 12px;
	text-decoration:underline;
}
.sidenavtext a:active{
	font-family: "Franklin Gothic Medium Cond", Verdana, "Arial Narrow";
	font-weight: bold;
	color: #666666;
	font-size: 12px;
	text-decoration:none;
}
-->
</style>
<script language="JavaScript" type="text/javascript">
<!--
function ChangePage(Opt_List)
{
var page_id = Opt_List.options[Opt_List.selectedIndex].value;
page_id = page_id * 1;
window.location="ezed_content.php?page_id=" + page_id ;
}
//-->
</script>
<link href="css/edit1.css" rel="stylesheet" type="text/css">
</head>

<body bgcolor="443B34">
<div align="center">
<table width="1170" border="1" cellpadding="0" cellspacing="0" bordercolor="#000000" bgcolor="#FFFFFF">
  <tr> 
    <td valign="top" bordercolor="#FFFFFF"><div align="center"> 
        <table width="100%" border="0" cellpadding="0" cellspacing="0" bordercolor="#999999">
          <tr bgcolor="#FFFFFF">
            <td colspan="4"><table border="0" cellpadding="8" cellspacing="0" width="1000">
                  
                <tr> 
                  <td height="50px"><div align="left"><span class="subhead">Simple2Edit Schedule Management </span> 
                      <?php if($_SESSION['MM_UserGroup'] == 3) {echo "<br><span class='warning'>You are logged in as an administrator</span>";} ?>
                  </div></td>
                  <td>&nbsp;</td>
                </tr>
            </table></td> 
          </tr>
          <tr>
            <td width="12%" align="center" valign="top">
			  <table width="189" cellspacing="0" cellpadding="0" border="0">
			  <tr><td class="sidenav" bgcolor="#DFF1FF" height="40px" ><div class="sidenavtext" style="margin-left:10px;border-bottom:thin;"><a href="ezed_admin.php">Main Menu </a></div></td></tr>
			 <tr>
			   <td height="40px" bgcolor="#E6E6E6" class="sidenav"><div style="margin-left:5px; overflow:hidden; width:170px;"><select style="width:190px;" class="buttons" name=page_id onChange="ChangePage(this);">
  	<option value="">Select a page to edit</option>
    <?php
				  	while($row_getPages = mysqli_fetch_assoc($getSidePages))
					{
						echo "<option value=" . $row_getPages['page_id'] . ">" . $row_getPages['name'] . "</option>";
					}
					 mysqli_data_seek($getPages,0);
					?>
  </select></div></td>
			 </tr>
			 <tr><td class="sidenav" height="40px"><div class="sidenavtext" align="left" style="margin-left:10px;border-bottom:thin;"><a href="ezed_file_management.php">Manage Files </a></div></td>
			 </tr>
			 <tr><td class="sidenav" height="40px" ><div class="sidenavtext" align="left" style="margin-left:10px;border-bottom:thin;"><a href="ezed_image_management.php">Manage Images</a></div></td></tr>
			 <?php 
					if($totalSchedules != 0) { ?> <tr><td class="sidenav" height="40px" ><div class="sidenavtext" align="left" style="margin-left:10px;border-bottom:thin;"><a href="ezed_schedule_management.php">Manage Schedules</a></div></td></tr><?php } ?>
			  <?php if($placecodes == 'Y' || $_SESSION['MM_UserGroup'] == 3) {?><tr><td class="sidenav" height="40px" ><div align="left" style="margin-left:10px;border-bottom:thin;"><span class="sidenavtext"><a href="ezed_placecode_management.php">Manage Placecodes</a></span><span class="text"> <a style="text-decoration:none;" href="#" title="Go here to add code from external sources (Javascript, iframes, etc) into a page">(?)</a></span></div></td></tr><?php } ?>
			 <?php if($num_galleries >0) { echo "<tr><td class='sidenav' height='40px' ><div  align='left' style='margin-left:10px;border-bottom:thin;'><span class='sidenavtext'>Manage Photo Galleries</span><br>"; while($gallery = mysqli_fetch_assoc($get_galleries)) { echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href='ezed_gallery_management.php?gallery=" . $gallery['gallery_type'] . "'><span class='text'>" . $gallery['gallery_name'] . "</span></a><br>"; } echo "</div></td></tr>"; }?>
			 <?php if($page_tag == 'Y' || $_SESSION['MM_UserGroup'] == 3) {?><tr><td class="sidenav" height="40px" ><div  align="left" style="margin-left:10px;border-bottom:thin;"><span class="sidenavtext"><a href="ezed_page_tag_editor.php">Page tag editor</a></span><span class="text"> <a style="text-decoration:none;" href="#" title="Use this to update the page Title, Keyword and Description tags">(?)</a></span></div></td></tr><?php } ?>
			 <?php if($page_script == 'Y' || $_SESSION['MM_UserGroup'] == 3) {?><tr><td class="sidenav" height="40px" ><div  align="left" style="margin-left:10px;border-bottom:thin;"><span class="sidenavtext"><a href="ezed_script_management.php">Page script editor</a></span><span class="text"> <a style="text-decoration:none;" href="#" title="Use this to add or update scripts in the header or footer of a page">(?)</a></span></div></td></tr><?php } ?>
			 <tr><td class="sidenav" height="40px" ><div class="sidenavtext" align="left" style="margin-left:10px;border-bottom:thin;"><a href="ezed_my_account.php">My Account</a></div></td></tr>
			 <?php if($_SESSION['MM_UserGroup'] == 3) { ?><tr><td class="sidenav" height="40px" ><div class="sidenavtext" align="left" style="margin-left:10px;border-bottom:thin;"><a href="ezed_site_admin.php">Site Administration </a></div></td></tr> <?php } ?>
			 <tr><td class="sidenav" height="40px"><div class="sidenavtext" align="left" style="margin-left:10px;border-bottom:thin;"><a onClick="window.location='<?php echo $logoutAction ?>'" href="#">Logout</a></div></td></tr>
		    </table></td> 
            <td width="0%" valign="top" bgcolor="#DFF1FF">&nbsp;</td>
            <td width="88%" valign="top"><div style="background-color:#DFF1FF; height:5px;"></div><form id="schededit" name="schededit" method="post" action="<?php $_SERVER['../PHP_SELF'];?>">
			   <table width="95%" border="0" cellspacing="0" cellpadding="8">
	
			<tr>
			  <td width="181" valign="top"><div class="sectionbox"><p align="left" class="text">Select type of schedule <br />
			  to display. <br>
			  Click <a href="http://www.studioofdance.com/simple2edit/help/schedule_types.htm" target="_blank" >here</a> for examples
</p>
			      <p>
				<label>
				<select name="stype" size="1">
                  <option value="<?php echo $row_getSchedInfo['sched_type']; ?>"selected><?php echo $row_getSchedInfo['sched_type']; ?></option>
                  <option>Across</option>
                  <option>Across BY studio</option>
                  <option>Down</option>
                  <option>Down BY studio</option>
                  <option>Down BY studio2</option>
                  <option>Class</option>
                </select>
				</label>
			  </p>
			  </div></td>
			  <td width="254" valign="top"><div class="sectionboxauto"><span class="text">Select information to display on schedule page. </span>
			    <br>
			    <br />
                  <span class="text">
                  <label>
                  <input name="checkages" type="checkbox"<?php if($row_getSchedInfo['ages'] == 'y') echo 'checked="checked"'; ?> />
                    Ages</label>
                  <label>
                  <input name="checkteacher" type="checkbox"<?php if($row_getSchedInfo['teacher'] == 'y') echo 'checked="checked"'; ?>/>
                    Teachers</label>
                  
                  <input name="checkstudio" type="checkbox"<?php if($row_getSchedInfo['studio'] == 'y' || ($_POST['checkstudio'] == 'on')) echo 'checked="checked"'; ?> />
                    <strong>Studios</strong>		          <br />
		          <br />
                  </span>
			  </div>		      </td>
			  <td width="256">&nbsp;</td>
			</tr>
			<tr>
			  <td class="text"><p align="left">
			    <input name="add" type="submit" class="buttons" id="add" value="Add classes" />
			  </p>			  </td>
			  <td>
			    <div align="left">
  <input name="Save2" type="submit" class="buttons" value="Save changes and upload" />
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></td>
			</tr>
  </table><div align="left">
	  <p class="text"><?php if($newclasses == 'Y') { echo "<span class='warning'>You have added new classes to your schedule but they not been updated to the website.<br>To update the website with the new classes, click on the Save and Upload button</span>"; } ?>To change a class to a different day, select that day from the drop-down list. <br>
	    To remove a class, select &quot;Remove&quot; from the &quot;Day&quot; drop-down list and click the &quot;Save changes and upload&quot; button.
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
	      </span> </p></div>
	  <table width="100%" cellpadding="" cellspacing="1" bgcolor="#CCCCCC">
        <tr>
          <th width="104" class="text" scope="col"><strong>Day</strong></th>
          <th width="80" class="text" scope="col"><div align="left"><strong>From</strong></div></th>
          <th width="82" class="text" scope="col"><div align="left"><strong>To</strong></div></th>
          <th width="66" class="text" scope="col"><strong>AM/PM</strong></th>
          <th width="130" class="text" scope="col"><strong>Name</strong></th>
          <th width="82" class="text" scope="col"><strong>Grades/Ages</strong></th>
          <th width="61" class="text" scope="col"><strong>Teacher</strong></th>
          <th width="64" class="text" scope="col"><strong>Studio</strong></th>
          <th width="83" class="text" scope="col"><strong>Class<br>
            Highlight</strong></th>
          <th width="73" class="text" scope="col"><strong>Class<br>
            Status</strong></th>
        </tr>
        <?php 
		if($firstTime){
			$size = $totalRows_getSchedule;
		} else {
			$size = count($_POST['day']);
		}
		// create counter to work as array index on text input boxes below 
		$i = 0; 
		while($i <= $size -1) { 
			if($firstTime) {
				$day = $row_getSchedule['day'];
				$start_h = $row_getSchedule['class_start_h'];
				$start_m = $row_getSchedule['class_start_m'];
				$end_h = $row_getSchedule['class_end_h'];
				$end_m = $row_getSchedule['class_end_m'];
				$ampm = $row_getSchedule['am_pm'];
				$name = $row_getSchedule['name'];
				$ages = $row_getSchedule['ages'];
				$teacher = $row_getSchedule['teacher'];
				$studio = $row_getSchedule['studio'];
				$highlight = $row_getSchedule['classHL'];
				$class_status = $row_getSchedule['class_status'];
				$row_getSchedule = mysqli_fetch_assoc($getSchedule);
			} else {
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
				$class_status = $iclass_status[$i];
			}
		?>
        <tr>
          <td><select name="day[<?php $i ?>]">
              <option value="<?php echo $day; ?>"selected><?php echo $day; ?></option>
              <option value="Monday">Monday</option>
              <option value="Tuesday">Tuesday</option>
              <option value="Wednesday">Wednesday</option>
              <option value="Thursday">Thursday</option>
              <option value="Friday">Friday</option>
              <option value="Saturday">Saturday</option>
              <option value="Sunday">Sunday</option>
              <option value="Remove">Remove</option>
            </select>          </td>
          <td><input name="class_start_h[<?php $i ?>]" type="text" id="class_start_h" value="<?php echo $start_h; ?>" maxlength="2" class="time"/><strong class="subhead">:</strong><input name="class_start_m[<?php $i ?>]2" type="text" id="class_start_m[<?php $i ?>]" value="<?php echo $start_m; ?>" size="1" maxlength="2" class="time" /></td><td><input name="class_end_h[<?php $i ?>]"  type="text" id="class_end_h" value="<?php echo $end_h; ?>" size="1" maxlength="2" class="time" /><strong class="subhead">:</strong><input name="class_end_m[<?php $i ?>]2"  type="text" id="class_end_m[<?php $i ?>]" value="<?php echo $end_m; ?>" size="1" maxlength="2" class="time" /></td><td><div align="center">
              <select name="am_pm[<?php $i ?>]">
                <option value="<?php echo $ampm; ?>"selected><?php echo $ampm; ?></option>
                <option value="AM">AM</option>
                <option value="PM">PM</option>
              </select>
          </div></td>
          <td><input name="name[<?php $i ?>]" type="text" id="name" value="<?php echo $name; ?>" size="20" /></td>
          <td><input name="ages[<?php $i ?>]" type="text" id="ages" value="<?php echo $ages; ?>" size="10" /></td>
          <td><input name="teacher[<?php $i ?>]" id="teacher"type="text" value="<?php echo $teacher; ?>" size="10" /></td>
          <td><input name="studio[<?php $i ?>]" id="studio" type="text" value="<?php echo $studio; ?>" size="10" /></td>
          <td><select name="highlight[<?php $i ?>]">
              <option value="<?php echo $highlight; ?>"selected><?php echo $highlight; ?></option>
              <option value="Normal">Normal</option>
              <option value="Bold">Bold</option>
          </select></td>
          <td><select name="class_status[<?php $i ?>]">
              <option value="<?php echo $class_status; ?>"selected><?php echo $class_status; ?></option>
              <option value="OPEN">OPEN</option>
              <option value="FULL">FULL</option>
          </select></td>
        </tr>
        <?php
			//$row_getSchedule = mysqli_fetch_assoc($getSchedule);
		$i++; 
		}  ?>
      </table>
	  <p align="center">
	    
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input name="Save" type="submit" class="subhead" value="Save changes and upload" />
</p>
	</form></td>
          </tr>
        </table>
          
        </div>    </td>
  </tr>
</table>
</div>
</body>
</html>
<?php
}
mysqli_free_result($getSchedule);
mysqli_free_result($updateSchedule);
mysqli_free_result($getSchedInfo);
?>