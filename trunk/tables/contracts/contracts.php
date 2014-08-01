<?php

class tables_contracts {

	//Permissions
	function getPermissions(&$record){
		//Check if the user is logged in & what their permissions for this table are.
		if( isUser() ){
			$userperms = get_userPerms('contracts');
			if($userperms == "view")
				return Dataface_PermissionsTool::getRolePermissions("READ ONLY"); //Assign Read Only Permissions
			elseif($userperms == "edit")
				return Dataface_PermissionsTool::getRolePermissions(myRole()); //Assign Permissions based on user Role (typically USER)
		}

		//Default: No Access
		return Dataface_PermissionsTool::NO_ACCESS();
	}


	function getTitle(&$record){
	//	return $record->val('contract_type') . " Contract: " . $record->val('customer') . " - " . $record->val('address');
		return $record->val('contract_type') . " Contract: " . $record->val('address');
	}

	function titleColumn(){
		return 'CONCAT(customer," - ",address)';
	}	


	function contract_type__default(){
		return "Preventative Maintenance";
	}

//	function section__details(&$record){
//		return array(
//			'content' => '',
//			'class' => 'main',
//			'label' => 'Site Details',
//			'order' => 1
//		);
//	}

	function contract_amount__htmlValue($record){
		$bc = $record->val('billing_cycle');
		$bills = sizeof($bc);
		$charge_per_bill = round($record->val('contract_amount') / $bills, 2);
		return '$' . $record->val('contract_amount') . ' (Billed in increments of $' . $charge_per_bill . ', per the Billing Cycle)';
	}

	//To get this to show on the view page check out: http://xataface.com/forum/viewtopic.php?t=4220, or just create a Table View
	//function charge_per_bill__htmlValue($record){
	//	$bc = $record->val('billing_cycle');
	//	$bills = sizeof($bc);
	//	$charge_per_bill = round($record->val('contract_amount') / $bills, 2);
	//	return $charge_per_bill;
	//}
	
}
?>