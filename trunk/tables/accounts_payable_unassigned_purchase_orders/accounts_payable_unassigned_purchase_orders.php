<?php

class tables_accounts_payable_unassigned_purchase_orders {

//SQL to create VIEW: (SELECT purchase_order_id, assigned_voucher_id, vendor_id FROM `purchase_order_inventory` WHERE assigned_voucher_id IS NULL) UNION (SELECT purchase_order_id, assigned_voucher_id , vendor_id FROM `purchase_order_service` WHERE assigned_voucher_id IS NULL)

	function getTitle(&$record){
		return 'Purchase Order: ' . $record->val('purchase_order_id');
	}
	
	
}
?>
