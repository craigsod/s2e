<?php
// Reads gallery directory for image files and build JSON file
	
//****************** CHANGE THIS VALUE FOR SPECIFIC GALLERY LOCATION ******************
$path = "gallery1";
//*************************************************************************************
//****** ALSO CHANGE GALLERY FILE NAME BELOW ******


// Read the gallery directory for images
// Read images in the myalbum_files directory
$handle = opendir($path);

// Loop over directory and store names of images into array
while (false !== ($file = readdir($handle))) {
	if ($file != "." && $file != "..") {
		if (strpos($file, '.gif',1)||strpos($file, '.jpg',1)||strpos($file, '.JPG',1) ) {
			$str[] = $file;
		}
	}
}

// Sort files in array
natsort($str);

$sortstr = array();
$sortstr = $str;
unset($str);
$str = array();
foreach($sortstr as $value) {
	$str[] = $value;
}


// Store first image into $start_img variable to be used at bottom of XML file
$start_img = $str[0];


// First part of XML file


//****************** UPDATE THIS VALUE FOR SPECIFIC GALLERY FILE NAME ******************
$gallery_file = "gallery1.json";
//**************************************************************************************

$fh = fopen($gallery_file, 'w');


// Write first part to file
fwrite($fh, "data = [");

// Loop through array and create string to be written to XML file
// This assumes the sort process above puts the files in order with the 
// _s. file listed second.
$cnt = count($str);
$i = 0;
$img = 1;
while($i <= $cnt -1) {
	$str[$i] = '{ image: "' . $path . "/" . $str[$i] . '" },';
	$i++;
	$img++;
}

//Loop through arrary and write to XML file
$i = 0;
while($i <= $cnt -1) {
	fwrite($fh, $str[$i] . "\n");
	//echo $str[$i] . "<BR>";
	$i++;
}

// Write second part to file
fwrite($fh, "];");
fclose($fh);

?>