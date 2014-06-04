<?php

class tables_call_slip_additional_materials {
	
	function field__item_total($record){
		return number_format($record->val("quantity") * $record->val("sale_price"),2);
	}
	
}

?>
