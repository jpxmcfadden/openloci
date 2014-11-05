<?php

class tables__payroll_config {

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

	//Remove the "view", "show all", "list", and "find" tabs - since there is only 1 record in this table.
	function init(){
		echo "<style>#record-tabs-view{display: none;} #actions-menu-show_all{display: none;} #table-tabs-list{display: none;} #table-tabs-find{display: none;}</style>";
	}

	function getTitle(&$record){
		return "Payroll Configuration";
	}
	
	function titleColumn(){
		return "Payroll Configuration";
	}
	
	function beforeSave(&$record){
	}

	//Redirect to Dashboard on Save
	function block__before_form(){
		if(isset($_GET['--saved'])){
			$msg = 'Payroll Configuration Successfully Changed';
			header('Location: index.php?-action=dashboard'.'&--msg='.urlencode($msg)); //Go to dashboard.
		}
	}

	//Blank this to properly redirect to Dashboard on Save
	function after_action_edit(){
	}

	function afterRemoveRelatedRecord(&$record){
		//Take 'related' record and get the 'actual' associated record
		$rec = $record->toRecord();
		
		//Delete the record.
		$rec->delete();
	}
	
}

?>


