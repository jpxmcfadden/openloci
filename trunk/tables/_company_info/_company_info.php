<?php

class tables__company_info {

	//Permissions
	function getPermissions(&$record){
		//Check if the user is logged in & what their permissions for this table are.
		if( isAdmin() ){
			$perms = Dataface_PermissionsTool::getRolePermissions(myRole()); //Assign Permissions based on user Role (typically SYSTEM)
			//Remove new & delete options
				unset($perms['new']);
				unset($perms['delete']);
			return $perms;
		}
		elseif( isUser() ){
			return Dataface_PermissionsTool::getRolePermissions("READ ONLY"); //Assign Read Only Permissions
		}

		//Default: No Access
		return Dataface_PermissionsTool::NO_ACCESS();
	}

	//Remove the "view", "show all", "list", and "find" tabs - since there is only 1 record in this table.
	function init(){
		echo "<style>#record-tabs-view{display: none;} #actions-menu-show_all{display: none;} #table-tabs-list{display: none;} #table-tabs-find{display: none;}</style>";
	}
		
	//Redirect to Dashboard on Save
	function block__before_form(){
		if(isset($_GET['--saved'])){
			$msg = 'Company Info Successfully Changed';
			header('Location: index.php?-action=dashboard'.'&--msg='.urlencode($msg)); //Go to dashboard.
		}
	}

	//Blank this to properly redirect to Dashboard on Save
	function after_action_edit(){
	}

}

?>