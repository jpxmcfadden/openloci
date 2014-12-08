<?php

class tables_purchase_order_vehicle_items{

	//Permissions
		function getPermissions(&$record){
		return Dataface_PermissionsTool::getRolePermissions('READ ONLY');
			//Check if the user is logged in & what their permissions for this table are.
			if( isUser() ){
				$userperms = get_userPerms('purchase_order_vehicle');
				if($userperms == "view")
					return Dataface_PermissionsTool::getRolePermissions("READ ONLY"); //Assign Read Only Permissions
				elseif($userperms == "edit" || $userperms == "received" || $userperms == "post"){
					if ( isset($record) ){
						$po_record = df_get_record('purchase_order_vehicle', array('purchase_id'=>$record->val('purchase_order_id')));
						if(	isset($po_record) && $po_record->val('post_status') != '')
							//return Dataface_PermissionsTool::getRolePermissions('NO_EDIT_DELETE');
							return Dataface_PermissionsTool::getRolePermissions('READ ONLY');
					}
					return Dataface_PermissionsTool::getRolePermissions(myRole()); //Assign Permissions based on user Role (typically USER)
				}
			}

			//Default: No Access
			return Dataface_PermissionsTool::NO_ACCESS();
		}

}

?>
