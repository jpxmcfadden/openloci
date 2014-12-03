<?php

class tables_tool_inventory_purchase_history {

	function getTitle(&$record){
		$inventory_record = df_get_record('tool_inventory', array('tool_id'=>$record->val('tool_id')));
		return "Tool Inventory Purchase History for: " . $inventory_record->strval('item_name');
	}

	function purchase_order_id__renderCell(&$record){
		return '<a href="index.php?-table=purchase_order_tool&-action=browse&-recordid=purchase_order_tool%3Fpurchase_id%3D'.$record->strval('purchase_order_id').'">' . $record->strval('purchase_order_id') . '</a>';
	}
	
	function purchase_date__renderCell(&$record){
		return $record->strval('purchase_date');
	}

	function vendor__renderCell(&$record){
		$vendor_record = df_get_record('vendors',array('vendor_id'=>$record->strval('vendor')));
		if(isset($vendor_record)) //Since vendor is not a required field, this requires a check. Otherwise if empty will return error.
			return $vendor_record->val('vendor');
		return "";
	}
	
	function purchase_price__renderCell(&$record){
		return $record->strval('purchase_price');
	}

	function quantity_purchased__renderCell(&$record){
		return $record->strval('quantity_purchased');
	}

}

?>