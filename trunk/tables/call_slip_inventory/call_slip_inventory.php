<?php

class tables_call_slip_inventory {

	//Permissions
	function getPermissions(&$record){
		//Check if the user is logged in & what their permissions for this table are.
		if( isUser() ){
			$userperms = get_userPerms('call_slips');
			if($userperms == "view")
				return Dataface_PermissionsTool::getRolePermissions("READ ONLY"); //Assign Read Only Permissions
			elseif($userperms == "edit")
				return Dataface_PermissionsTool::getRolePermissions(myRole()); //Assign Permissions based on user Role (typically USER)
		}

		//Default: No Access
		return Dataface_PermissionsTool::NO_ACCESS();
	}

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

	//Calculated fields - for HTML Reports
	function field__item_cost($record){
		//If sale overide cost has been set in the call slip, use it
		if($record->val('sale_price') != null)
			return number_format($record->val('sale_price'),2);

		//Otherwise, pull the item / cost out of the 'inventory' table
		$inventory_record = df_get_record('inventory', array('inventory_id'=>$record->val('inventory_id')));
		
		//If item is set for cost overide, use 'sale_overide'
		if($inventory_record->val('sale_method') == "overide")
			return $inventory_record->val('sale_overide');
			
		//Otherwise, calculate the price from the sale average
		$customerRecord = df_get_record('customers', array('customer_id'=>$record->val('customer_id')));
		$markupRecords = df_get_records_array('customer_markup_rates', array('markup_id'=>$record->val('markup')));

		$purchase_price = $record->val('purchase_price');
		
		foreach ($markupRecords as $mr) {
			if($mr->val('to') == null)
				$no_limit = true;

			if( ($purchase_price >= $mr->val('from')) && ($purchase_price <= $mr->val('to') || $no_limit == true) ){
				$markup = $mr->val('markup_percent');
				break;
			}
		}

		$sale_price = number_format($purchase_price * (1+$markup),2,".","");
		
		return $sale_price;
	}

	function field__item_total($record){
		return number_format($record->val('sale_price') * $record->val('quantity'),2);
	}
	
}

?>