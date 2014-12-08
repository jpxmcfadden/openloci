<?php

class tables_vehicles {

	//Permissions
		function getPermissions(&$record){
			//Check if the user is logged in & what their permissions for this table are.
			if( isUser() ){
				$userperms = get_userPerms('vehicles');
				if($userperms == "view")
					return Dataface_PermissionsTool::getRolePermissions("READ ONLY"); //Assign Read Only Permissions
				elseif($userperms == "edit"){
					return Dataface_PermissionsTool::getRolePermissions(myRole()); //Assign Permissions based on user Role (typically USER)
				}
			}

			//Default: No Access
			return Dataface_PermissionsTool::NO_ACCESS();
		}

	function rel_vehicle_purchase_history__permissions($record){
		return array(
			'add new related record' => 0,
			'add existing related record' => 0,
			'remove related record' => 0,
			'delete related record' => 0			
		);
	}		
}

?>