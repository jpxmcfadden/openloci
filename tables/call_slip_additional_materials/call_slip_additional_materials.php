<?php

class tables_call_slip_additional_materials {
	
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

	function field__item_total($record){
		return number_format($record->val("quantity") * $record->val("sale_price"),2);
	}
	
}

?>
