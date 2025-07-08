<?php 
include_once('../../Connections/db_admin.php');
// Establish connection to database
mysqli_select_db($sodcustomer,$database);

// Get information from URL

$site = $_GET['site'];
$date = $_GET['date'];
$action = $_GET['action'];

echo "Site: ". $site;
exit();

$insert_log = "INSERT INTO s2e_log (site,date,action) VALUES ('$site','$date','$action')";
	
mysqli_query($sodcustomer,$insert_log) or die();
?>