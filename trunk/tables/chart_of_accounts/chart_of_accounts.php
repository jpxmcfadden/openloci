<?php

class tables_chart_of_accounts {

	function getPermissions(&$record){
		$role = "NO_EDIT_DELETE";
		return Dataface_PermissionsTool::getRolePermissions($role);
	}

	//Set the record title
	function getTitle(&$record){
		return "Account #".$record->val('account_number')." - ".$record->val('account_name');
		//return $record->val('call_id');
	}

	function titleColumn(){
		return 'CONCAT("Account #account_number - account_name")';
	}
	
	function renderRelatedRow(&$record){ //****THIS ISN'T WORKING*****//
		//$record = "general_ledger_journal";
		return "foo";
	}
}

?>