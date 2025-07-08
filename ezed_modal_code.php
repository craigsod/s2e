<link rel="stylesheet" href="colorbox/colorbox.css" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="colorbox/jquery.colorbox.js"></script>
<script>
	$(document).ready(function(){
		//Examples of how to assign the Colorbox event to elements
		$(".modalhelp").colorbox({transition:"elastic", rel:'nofollow'});
		$(".modalhelp600").colorbox({transition:"elastic", width:600, height:450});
		$(".modalhelp800").colorbox({transition:"elastic", width:800, height:600});
		$(".youtube").colorbox({iframe:true, innerWidth:900, innerHeight:550});
		$(".callbacks").colorbox({
					onClosed:function(){ window.location="ezed_placecode_management.php"; }
				});
	});
</script>