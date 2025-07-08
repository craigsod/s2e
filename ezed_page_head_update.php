<?php
require_once('../../Connections/studioAdmin_i.php');
include_once('ezed_create_HTML_function.php');

mysqli_select_db($studioAdmin, $database_studioAdmin);

if(isset($_POST['title']) && $_POST['title'] <> "") {
	$page_id = $_POST['page_id'];
	$title = mysql_real_escape_string($_POST['title']);
	$keyword = mysql_real_escape_string($_POST['keyword']);
	$description = mysql_real_escape_string($_POST['description']);

	// Update page record
	$update_query = "UPDATE pages SET title = '$title', keywords = '$keyword', description = '$description' WHERE page_id = '$page_id'";
	echo $update_query;
	exit();
	mysql_query($update_query, $studioAdmin) or die(mysql_error());
	createHTMLfile($page_id, $studioAdmin,$database_studioAdmin,$studioAdmin,$database_studioAdmin);
	
	header("Location: ezed_page_head_editor.php");
} else {
	header("Location: ezed_page_head_editor.php");
}
?>