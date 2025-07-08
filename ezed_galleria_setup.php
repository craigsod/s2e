<?php 
$additionalspace = 50;
$gallery_path = $_GET['path'];
$gallery_type = $_GET['gallerytype'];
$gallery_name = $_GET['galleryname'];
$gallery_file = $_GET['galleryfile'];
$gallery_width = $_GET['width'];
$gallery_width50 = $gallery_width;
$gallery_height = $_GET['height'];
$gallery_height50 = $gallery_height;

?>
<blockquote>
<p style='font-family:Arial, Helvetica, sans-serif; font-size:12px;'>&nbsp;</p>
<p style='font-family:Arial, Helvetica, sans-serif; font-size:12px;'><strong>Add these two lines to the &lt;head&gt; section of the header.php pages:</strong><br />
  &lt;script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js">&lt;/script><br />
  &lt;script src="galleria/galleria-1.2.9.min.js">&lt;/script><br />
</p>
<p style='font-family:Arial, Helvetica, sans-serif; font-size:12px;'>&nbsp;</p>
<p  style='font-family:Arial, Helvetica, sans-serif; font-size:12px;'><strong>Make the following changes to the gallery_info table for the <?php echo $gallery_name; ?> record (do not include quote marks):</strong><br>
			&nbsp;&nbsp;&nbsp;Change gallery_path to: <strong><?php echo $gallery_path; ?></strong><BR>
			&nbsp;&nbsp;&nbsp;Change gallery_type to: <strong><?php echo $gallery_type; ?></strong><BR>
			&nbsp;&nbsp;&nbsp;Change gallery_file to: <strong><?php echo $gallery_file; ?></strong><br>
</p>
<p style='font-family:Arial, Helvetica, sans-serif; font-size:12px;'>&nbsp;</p>
<p style='font-family:Arial, Helvetica, sans-serif; font-size:12px;'><strong>&nbsp;Update pages that contain this gallery with this Galleria script code.</strong><br />Replace existing gallery code with the code below. Be sure to remove all parts of the old gallery code.</p>
<p style='font-family:Arial, Helvetica, sans-serif; font-size:12px;' dir="ltr">&lt;script type=&quot;text/javascript&quot; src=<?php echo $gallery_file; ?>&gt;&lt;/script&gt;<br>
  &lt;div style=&quot;width:<?php echo trim($gallery_width,"'"); ?>px; height:<?php echo trim($gallery_height,"'"); ?>px; background-color:#EAEAEA;&quot; id=&quot;galleria&quot;&gt;&lt;/div&gt;<br>
  &lt;script&gt;<br>
  // var mydata = JSON.parse(data);<br>
  Galleria.loadTheme('galleria/themes/classic/galleria.classic.min.js');<br>
  Galleria.run('#galleria', {<br>
  autoplay: 3000, // will move forward every 7 seconds<br>
  dataSource: data,<br>
  showImagenav: true,<br>
  width: <?php echo trim($gallery_width50,"'"); ?>,// set 50 above image width - set galleria div width to same (see above)<br>
  height: <?php echo trim($gallery_height50,"'"); ?>, // set 50 above image height<br>
  imageMargin: 0,<br>
  imageCrop: false,<br>
  easing: galleria,<br>
  // Use CSS to change background, border, etc<br>
  });<br>
  &lt;/script&gt;</p>
<p style='font-family:Arial, Helvetica, sans-serif; font-size:12px;' dir="ltr">&nbsp;</p>
<p style='font-family:Arial, Helvetica, sans-serif; font-size:12px;' dir="ltr">Close this window tab when done. <br>
</p>
</blockquote>


