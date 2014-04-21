<?php

//Use global scope variables for totals so that other sections can access them.
$totals_gross_income = 0;
$totals_net_income = 0;
$totals_deductions = 0;
		
class tables__payroll_config {

	function getTitle(&$record){
		return "Payroll Configuration";
	}
	
	function titleColumn(){
		return "Payroll Configuration";
	}

	//Permissions
/*	function getPermissions(&$record){
		//First check if the user is logged in.
		if( isUser() ){
			//Check status, determine if record should be uneditable.
			if ( isset($record) ){
				//if(	$record->val('status') == "Posted" && !isset($_GET['-status_post'])) //No edit after Posted
				if(	$record->val('status') == "Closed" && !isset($_GET['confirm']) ) //No edit after Closed
					return Dataface_PermissionsTool::getRolePermissions('NO_EDIT_DELETE');

				return Dataface_PermissionsTool::getRolePermissions(myRole());
			}
		}
		else
			return Dataface_PermissionsTool::NO_ACCESS();
	}
*/
	
	function beforeSave(&$record){
	}

	function block__before_form(){
		if(isset($_GET['--saved'])){
			$msg = 'Payroll Configuration Successfully Changed';
			header('Location: index.php?-action=dashboard'.'&--msg='.urlencode($msg)); //Go to dashboard.
		}
	}
	
	
	function after_action_edit(){
	}
	
}

?>


