<?php

class tables_inventory_purchase_history {

	function getTitle(&$record){
		$inventory_record = df_get_record('inventory', array('inventory_id'=>$record->val('inventory_id')));
		return "Inventory Purchase History for: " . $inventory_record->strval('item_name');
	}

	function purchase_order_id__renderCell(&$record){
		return '<a href="index.php?-table=purchase_order_inventory&-action=browse&-recordid=purchase_order_inventory%3Fpurchase_id%3D'.$record->strval('purchase_order_id').'">' . $record->strval('purchase_order_id') . '</a>';
	}
	
	function purchase_date__renderCell(&$record){
		return $record->strval('purchase_date');
	}

	function vendor__renderCell(&$record){
		$vendor_record = df_get_record('vendors',array('vendor_id'=>$record->strval('vendor')));
		return $vendor_record->val('vendor');
	}
	
	function purchase_price__renderCell(&$record){
		return $record->strval('purchase_price');
	}

	function quantity_purchased__renderCell(&$record){
		return $record->strval('quantity_purchased');
	}

}

?>