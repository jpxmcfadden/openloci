<?php

class tables_accounts_receivable_batch_vouchers {

	//Permissions
	function getPermissions(&$record){
		//Check if the user is logged in & what their permissions for this table are.
		if( isUser() ){
			$userperms = get_userPerms('accounts_receivable');
			if($userperms == "view")
				return Dataface_PermissionsTool::getRolePermissions("READ ONLY"); //Assign Read Only Permissions
			elseif($userperms == "edit" || $userperms == "post"){
				return Dataface_PermissionsTool::getRolePermissions(myRole()); //Assign Permissions based on user Role (typically USER)
			}
		}

		//Default: No Access
		return Dataface_PermissionsTool::NO_ACCESS();
	}

	function afterSave(&$record){
		$voucher_record = df_get_record('accounts_receivable', array('voucher_id'=>$record->val('voucher_id')));
		$voucher_record->setValue('batch_id',$record->val('batch_id'));
		$voucher_record->save();
	}

	function beforeDelete(&$record){
		$voucher_record = df_get_record('accounts_receivable', array('voucher_id'=>$record->val('voucher_id')));
		$voucher_record->setValue('batch_id',"");
		$voucher_record->save();
	}
	
}
?>
