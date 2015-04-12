<?php

class tables_dashboard {

	//Permissions
		function getPermissions(&$record){
			//Check if the user is logged in & what their permissions for this table are.
			if( isUser() ){
				$perms = Dataface_PermissionsTool::getRolePermissions("READ ONLY");
				$perms['find']=0;
				$perms['list']=0;
				$perms['show all']=0;
				return $perms;
			}

			//Default: No Access
			return Dataface_PermissionsTool::NO_ACCESS();
		}
}
		
?>