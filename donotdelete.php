<?php 
require_once('../../Connections/studioAdmin_i.php');

function record_empty_content($site_name, $date, $url) {
	$update_log = "http://www.studioofdance.com/db/record_empty_content.php?site_name=" . $site_name . "&date=" . $date . "&url=" . $url;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $update_log);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$response = curl_exec($ch);
	curl_close($ch);
}

$date = urlencode(date("n/j/Y g:i:s a"));

// Get site information from site table
mysqli_select_db($studioAdmin, $database_studioAdmin);
$query_getSiteInfo = "SELECT * FROM site";
$getSiteInfo = mysqli_query($studioAdmin, $query_getSiteInfo) or die(db_error_handle());
$row_getSiteInfo = mysqli_fetch_assoc($getSiteInfo);
	
$site_name = $row_getSiteInfo['studio_name'];
$css_path = $row_getSiteInfo['css_path'];
$head_image = $row_getSiteInfo['head_image'];
$url = $row_getSiteInfo['URL'];

//*********************************************************************
//  GET ALL THE PAGES TO POPULATE THE SIDEBAR MENU
//********************************************************************
$query_getSidePages = "SELECT * FROM pages WHERE editable = 'Y' ORDER BY page_id";
$getSidePages = mysqli_query($studioAdmin,$query_getSidePages) or die(db_error_handle());

// Loop through all pages
while($row_getPages = mysqli_fetch_assoc($getSidePages)){
	$colname_getContent = $row_getPages['page_id'];
	
	// ************************************************************
	// Select content record where status = A and page_id = GET[page_id]
	// ************************************************************
	$query_getContent = "SELECT * FROM content WHERE status = 'A' AND page_id = '$colname_getContent'";
	$getContent = mysqli_query($studioAdmin, $query_getContent);
	$row_getContent = mysqli_fetch_assoc($getContent);
	
	// If content record is empty, update SOD database
	// record_empty_content($site, $date, $url)
	// Check first that page is not the practice page
	if($colname_getContent != '99') {
		if($row_getContent['contents'] == '') {
			// record empty page to SOD database
			//record_empty_content($site_name, $date, $url);
			
		}
	}
}
?>