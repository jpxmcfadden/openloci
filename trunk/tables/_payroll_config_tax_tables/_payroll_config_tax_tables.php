<?php
		
class tables__payroll_config_tax_tables {

	//Permissions
	function getPermissions(&$record){
		//Check if the user is logged in & what their permissions for this table are.
		if( isUser() ){
			$userperms = get_userPerms('payroll_config');
			if($userperms == "view")
				return Dataface_PermissionsTool::getRolePermissions("READ ONLY"); //Assign Read Only Permissions
			elseif($userperms == "edit"){
				$perms = Dataface_PermissionsTool::getRolePermissions(myRole()); //Assign Permissions based on user Role (typically USER)
				//Remove new & delete options
					unset($perms['new']);
					unset($perms['delete']);
				return $perms;
			}
		}

		//Default: No Access
		return Dataface_PermissionsTool::NO_ACCESS();
	}
}