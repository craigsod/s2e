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



// **********************************************************
// Get site information from SITE table
// **********************************************************
mysqli_select_db($studioAdmin, $database_studioAdmin);
$query_getSiteInfo = "SELECT * FROM site";
$getSiteInfo = mysqli_query($studioAdmin, $query_getSiteInfo);
$row_getSiteInfo = mysqli_fetch_assoc($getSiteInfo);


// Set form action to call itself
$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}


//*********************************************************************
//  GET ALL THE PAGES TO POPULATE THE SIDEBAR MENU
//********************************************************************
$query_getSidePages = "SELECT * FROM pages WHERE editable = 'Y' ORDER BY page_id";
$getSidePages = mysqli_query($studioAdmin, $query_getSidePages);
//$row_getPages = mysqli_fetch_assoc($getPages);


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
	header("Location: ezed_schededit.php?schedule_no=$schedule");
	exit;
}


$firstTime = !array_key_exists('name', $_POST);
if(!$firstTime)
{	
	//$size = $_POST['num_classes'];
	$size = 10;
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
			$i++;
			continue;
			// Use continue here to jump out and start loop over with next row?
			// Increment $i by one?
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
if(!$firstTime)
{
	mysqli_select_db($studioAdmin, $database_studioAdmin);
	$i = 0;
	while($i <= $icount -1)
		{
		mysqli_query($studioAdmin, "INSERT INTO schedule (schedule_no, status, day, class_start_h, class_start_m, class_end_h, class_end_m, am_pm, name, ages, teacher, studio, hour24, dayno, classHL) VALUES ('$schedule','A','$iday[$i]','$istart_h[$i]', '$istart_m[$i]', '$iend_h[$i]', '$iend_m[$i]', '$iampm[$i]', '$iname[$i]', '$iages[$i]', '$iteacher[$i]', '$istudio[$i]','$ihour24[$i]','$idayno[$i]','$ihighlight[$i]')");
		$i++;
		}
	header("Location: ezed_schededit.php?schedule_no=$schedule&newclasses=Y");
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
                  <td height="50px"><div align="left"><span class="subhead">Add  Classes</strong></span>
                  <?php if($_SESSION['MM_UserGroup'] == 3) {echo "<br><span class='warning'>You are logged in as an administrator</span>";} ?></div></td>
                  <td>&nbsp;</td>
                </tr>
            </table></td> 
          </tr>
          <tr>
            
            <td width="88%" valign="top"><div style="background-color:#DFF1FF; height:5px;"></div>
			<table width="971" border="1" cellpadding="4" cellspacing="0" bordercolor="#000000" bgcolor="#FFFFFF">

    <tr> <td width="844" valign="top" bordercolor="FFFFFF">
	<table width="959" border="0" cellpadding="4" cellspacing="0" bordercolor="#999999">
			<tr><td width="1000">
			 <form id="schededit" name="schededit" method="post" action="<?php $_SERVER['../PHP_SELF'];?>">
	  <p class="text">
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
		  <th width="126" rowspan="2" class="text" scope="col"><strong>Day</strong></th>
		  <th colspan="3" class="text" scope="col"><div align="center"><strong>From</strong></div></th>
		  <th colspan="2" class="text" scope="col"><div align="center"><strong>To</strong></div></th>
		  <th width="115" rowspan="2" class="text" scope="col"><strong>AM/PM</strong></th>
		  <th width="230" rowspan="2" class="text" scope="col"><strong>Name</strong></th>
		  <th width="82" rowspan="2" class="text" scope="col"><strong>Grades/Ages</strong></th>
		  <th width="68" rowspan="2" class="text" scope="col"><strong>Teacher</strong></th>
		  <th width="68" rowspan="2" class="text" scope="col"><strong>Studio</strong></th>
		  <th width="100" rowspan="2" class="text" scope="col"><strong>Class<br>
		    Highlight</strong></th>
		</tr>
		<tr>
		  <th width="30" class="text" scope="col">Hr</th>
		  <th width="8" class="text" scope="col">&nbsp;</th>
		  <th width="33" class="text" scope="col">Min</th>
		  <th class="text" scope="col">Hr</th>
		  <th class="text" scope="col">Min</th>
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
			<td>
			  <div align="right">
			    <input name="class_start_h[<?php $i ?>]" type="text" id="class_start_h" value="" maxlength="2" class="time"/></div></td><td><strong class="subhead">:</strong></td><td><div align="left"><input name="class_start_m[<?php $i ?>]" type="text" id="class_start_m[<?php $i ?>]" value="" size="1" maxlength="2" class="time" /></div></td><td width="35"><div align="right"><input name="class_end_h[<?php $i ?>]"  type="text" id="class_end_h" value="" size="1" maxlength="2" class="time" /></div></td><td width="41"><strong class="subhead">:</strong><input name="class_end_m[<?php $i ?>]"  type="text" id="class_end_m[<?php $i ?>]" value="" size="1" maxlength="2" class="time" /></td><td><div align="center">
				  <select name="am_pm[<?php $i ?>]">
				  <option value="PM"SELECTED>PM</option>
				  <option value="AM">AM</option>
				  <option value="PM">PM</option>
				  </select>
				</div></td>
			<td><input name="name[<?php $i ?>]" type="text" id="name" value="" size="30" /></td>
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
	    
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="Save" type="submit" class="subhead" value="Save new classes" />&nbsp;&nbsp;&nbsp;
      <a href="ezed_schededit.php?schedule_no=<?php echo $schedule; ?>"><span class="text">Return to Schedule Management page</span> </a></p>
	</form></td></tr></table>
  </td></tr></table>
			</td>
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
?>