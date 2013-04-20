function unhide_widget(value){
	var elem = document.getElementById(value+'_form_row');
	elem.style.display = "none";
}

function hide_widget(value){

	var elem = document.getElementById(value+'_form_row');
	elem.style.display = "table-row";
}