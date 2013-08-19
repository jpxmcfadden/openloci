<?php

class tables_call_slip_inventory {

	//This is for Call Slip Invoices
	function field__total_cost($record){
		$total_cost = number_format($record->val('quantity') * $record->val('sell_cost'),2);
		return $total_cost;
	}

	function beforeSave(&$record){
	
		$inventory_record = df_get_record('inventory', array('inventory_id'=>$record->val('inventory_id')));

		//If an inventory "purchase price" is empty - i.e. an item has just been added - set it.
		if ($record->val('purchase_price') == null){
			$purchase_price = ($inventory_record->val('last_purchase') > $inventory_record->val('average_purchase')) ? $inventory_record->val('last_purchase') : $inventory_record->val('average_purchase');
			$record->setValue('purchase_price', $purchase_price);
		}
			
		//If an overide cost has not been assigned, calculate
		//if ($cs_ir['sale_price'] == null){
			//If auto
			
			//If overide
			
			//Save
		
		
			//$inv_rec = df_get_record('inventory', array('inventory_id'=>$cs_ir['inventory_id']));
			//$csi_rec = df_get_record('call_slip_inventory', array('csi_id'=>$cs_ir['csi_id']));
			//$csi_rec->setValue('sell_cost', $inv_rec->val('sell_cost'));
			//$csi_rec->setValue('purchase_cost', $inv_rec->val('purchase_cost'));
			//$csi_rec->save();
		//}
	
	}
	
}

?>