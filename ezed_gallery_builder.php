<?php
require_once('../../Connections/studioAdmin_i.php');

	// Need to pull the following inforation from the database
	// Gallery file name
	// Gallery div ID name
	// Image width
	// Image height
	
	// Read gallery directory for image files and build JSON file
	
if(isset($_GET['gallery_id']) && $_GET['gallery_id'] <> '') {
	$gallery_id = $_GET['gallery_id'];
}

// Query for  gallery info
mysqli_select_db($studioAdmin, $database_studioAdmin);
$query_getGallery = "SELECT * FROM gallery_info WHERE gallery_id = '$gallery_id'";
$getGallery = mysqli_query($studioAdmin, $query_getGallery);
$row_getGallery = mysqli_fetch_assoc($getGallery);
$path = $row_getGallery['gallery_path'];
$type = $row_getGallery['gallery_type'];
$gallery_file = $row_getGallery['gallery_file'];
$gallery_name = $row_getGallery['gallery_name'];
$max_images = $row_getGallery['gal_max_img'];
$height = $row_getGallery['gal_h_img_size'];
$width = $row_getGallery['gal_img_size'];
$first_half = $row_getGallery['xml_first_pt'];
$second_half = $row_getGallery['xml_second_pt'];


// Read the gallery directory for images
// Read images in the myalbum_files directory
$handle = opendir("../" . $path);

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



// Create XML file
$myAlbumFile = "../" . $gallery_file;
$fh = fopen($myAlbumFile, 'w');


// Write first part to file
fwrite($fh, $first_half);

// Loop through array and create string to be written to XML file
// This assumes the sort process above puts the files in order with the 
// _s. file listed second.
$cnt = count($str);
$i = 0;
$img = 1;
while($i <= $cnt -1) {
	$str[$i] = '{ image: "' . $path . $str[$i] . '" },';
	$i++;
	$img++;
}

//Loop through arrary and write to XML file
$i = 0;
while($i <= $cnt -1) {
	fwrite($fh, $str[$i] . "\n");
	$i++;
}

// Write second part to file
fwrite($fh, $second_half);
fclose($fh);
	
echo "
	<script type='text/javascript' src='http://www.studioofdancemedia.com/" . $gallery_file . "'></script>

			<div id='" . $type . "' style='width:" . $width . "px; height:" . $height . "px; background-color:#EAEAEA;'><script>
// Add width, height and background of galleria div above so that it shows up in editing window
            Galleria.loadTheme('galleria/themes/classic/galleria.classic.min.js');
            Galleria.run('#" . $type . "', {
    autoplay: 5000, // will move forward every 5 seconds
    dataSource: data,
    showImagenav: false,
    width: " . $width . ", // set 50 above image width - set galleria div width to same (see above)
    height: " . $height . ", // set 50 above image height
    imageMargin: 0,
    imageCrop: true,
    // Use CSS to change background, border, etc
});
        </script></div>
	";
?>
