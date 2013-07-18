<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="Content-language" content="en-US" />
<title>Append Textfield</title>
<script src="jquery.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
	$(document).ready(function(){
		$("#addField").click( function() {
			$("#nameFields").append('<label>Name <input type="text" name="first_name[]" /></label><br/>');
		});
	});
</script>
</head>

<body>
	<form method="post" action="" id="nameForm">
		<div id="nameFields">
			<label>Name <input type="text" name="first_name[]" /></label><br/>
		</div>
		<input type="submit" value="submit" name="s" />
	</form>
	<a href="#" onclick="return false;" id="addField">Add New Field</a>
</body>
</html>



<script src="JS/jquery.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
var count = $('#nameFields p').size() + 1;
	jQuery(document).ready(function(){
		jQuery("#addField").click( function() {
			var text = '<p><input name="opgave_' + count + '" type="text" value="<?php print($opgave);?>" size="50"/><input name="oplossing_' + count + '" type="text" value="<?php print($oplossing);?>" size="18" /></p>';
			jQuery("#nameFields").append(text);
			count++;
		});
	});
</script>