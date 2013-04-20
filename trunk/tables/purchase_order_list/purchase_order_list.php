<?php

class tables_purchase_order_list {

	//This is for Call Slip Invoices
	function field__total_cost($record){
		$total_cost = number_format($record->val('quantity') * $record->val('cost_sale'),2);
		return $total_cost;
	}

}

?>