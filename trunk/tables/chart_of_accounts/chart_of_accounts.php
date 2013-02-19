<?php

class tables_chart_of_accounts {

	function getPermissions(&$record){
		$role = "NO_EDIT_DELETE";
		return Dataface_PermissionsTool::getRolePermissions($role);
	}
	
}

?>