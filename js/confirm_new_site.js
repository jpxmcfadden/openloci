//require <jquery.packed.js>

function getParameterByName( name )
{
  name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
  var regexS = "[\\?&]"+name+"=([^&#]*)";
  var regex = new RegExp( regexS );
  var results = regex.exec( jQuery(location).attr('href') );
  if( results == null )
    return "";
  else
    return decodeURIComponent(results[1].replace(/\+/g, " "));
}

//Disable multiple submit presses
jQuery(document).ready(function(){
	//jQuery("form:first").submit(function(){
	jQuery("input[type='submit']").click(function(){
		if(getParameterByName('-action') == "new")
			if(confirm("Do you wish to automatically create site based on the Billing Address?"))
				document.forms['new_customers_record_form'].elements['create_new_site'].value = "yes";
				//jQuery('input[name=create_new_site]').val('yes');
	});

});

