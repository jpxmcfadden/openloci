function flips(name){

	//var control = document.getElementById();
	
	//document.getElementById(flip_id).value = "foo";

//vendors-tax_id-label-wrapper

	//alert(name.value + 	document.getElementById(flip_id).value);

	alert(name);
		//if(control.style.visibility == "visible" || control.style.visibility == "")
		//	control.style.visibility = "hidden";
		//else
		//	control.style.visibility = "visible";
	
	//if(name.checked)
	//	alert("Y - "+name.checked);
	//else
	//	alert("N - "+name.checked);
}


//function onGridFieldChanged(el){
//var form = XataJax.load('XataJax.form');
//var field = jQuery('#inventory_id');
//var sellcost = form.findField(field, 'sell_cost');
//alert(field);
//}

//require <xatajax.form.core.js>
/*(function(){

    // Make onGridFieldChanged function in the global scope
     //  FOR DEMONSTRATION ONLY... YOU SHOULD USE YOUR OWN CONSISTENT
     // NAMESPACING AND NOT CLUTTER THE GLOBAL SCOPE
    window.onGridFieldChanged = onGridFieldChanged; 
*/
/*
    * A callback to handle when an ingredient is changed.  This is registered
    * in the dtg_recipe_ingredients fields.ini file.
    * @param {HTMLElement} el The field that was changed.
    */
	function onGridFieldChanged(el){
		var $ = jQuery;
		var form = XataJax.load('XataJax.form');
                // get the other fields in the current row
                //var taxPercentField = xform.findField(el, 'taxPercent');
                //var taxAmountField = xform.findField(el, 'taxPercent');
                //var priceField = xform.findField(el, 'price');
                //var totalField = xform.findField(el, 'price');

				var iid = $('#inventory_id');
				//var sellcost = form.findField(iid, 'sell_cost');

                //$( sellcost).val(1) );
				//alert(el);
				//alert($(iid).val());
				
				alert($(el).attr('id'))
      
      
      
   }
//})();