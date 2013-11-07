function unhide_widget(value){
	var elem = document.getElementById(value+'_form_row');
	elem.style.display = "none";
}

function hide_widget(value){
	var elem = document.getElementById(value+'_form_row');
	elem.style.display = "table-row";
}

function change_field(field, value){
	//alert(document.getElementByName(field).value);
	//alert(document.forms[0][field].value);
	//document.forms[0][field].value = 1;
	//alert(document.forms[0][field].value);
	//alert(document.forms[0].accounts_payable-vendor_id-wrapper.style.display);
	
	//document.forms[0][field].style.display = "table-row";
	
	//alert(document.forms[0][field].style.display);
	
	//elements[1].readOnly = "false";
	//document.forms[0][field].readOnly = "true";
	//document.forms[0][field].readOnly = false;

	//alert(document.forms[0][field].style.cursor);
	//alert(elements[1].style.cursor);
	//elements[1].readOnly = false;
	//elements[1].value = "foo";

//	elements = document.getElementsByClassName('xf-RecordBrowserWidget-displayField');
//	elements[1].style.display = "none";
	
//	alert(document.getElementsByName(field)[0].nextSibling.value);
//	document.getElementsByName(field)[0].nextSibling.value = value;

}
