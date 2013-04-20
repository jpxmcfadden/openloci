<?php

class tables_call_slip_inventory {

	//This is for Call Slip Invoices
	function field__total_cost($record){
		$total_cost = number_format($record->val('quantity') * $record->val('sell_cost'),2);
		return $total_cost;
	}
	
}

?>