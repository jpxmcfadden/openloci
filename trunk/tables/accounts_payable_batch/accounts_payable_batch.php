<?php

class tables_accounts_payable_batch {

	//Permissions
	function getPermissions(&$record){
		//Check if the user is logged in & what their permissions for this table are.
		if( isUser() ){
			$userperms = get_userPerms('accounts_payable');
			if($userperms == "view")
				return Dataface_PermissionsTool::getRolePermissions("READ ONLY"); //Assign Read Only Permissions
			elseif($userperms == "edit" || $userperms == "post"){
				//Check status, determine if record should be uneditable.
				if ( isset($record) && $record->val('post_status') == 'Posted')
						return Dataface_PermissionsTool::getRolePermissions('NO_EDIT_DELETE');
				return Dataface_PermissionsTool::getRolePermissions(myRole()); //Assign Permissions based on user Role (typically USER)
			}
		}

		//Default: No Access
		return Dataface_PermissionsTool::NO_ACCESS();
	}

	function after_action_new($params=array()){
		$record =& $params['record'];
		$msg = "Voucher records added to Batch #" . $record->val('batch_id');
		header('Location: index.php?-action=list&-table=accounts_payable'.'&--msg='.urlencode($msg)); //Go back to Accounts Payable
		exit;
	}
	
	function after_action_edit($params=array()){
		$record =& $params['record'];
		$msg = "Voucher records in Batch #" . $record->val('batch_id') . ' modified.';
		header('Location: index.php?-action=list&-table=accounts_payable'.'&--msg='.urlencode($msg)); //Go back to Accounts Payable
		exit;
	}

	
	//Add included vouchers the view tab
	function section__vouchers(&$record){

		$childString = "";	
	
		$voucherRecords = $record->getRelatedRecords('vouchers');

		$childString .= '<table class="view_add"><tr>
							<th>Voucher ID</th>
							<th>Voucher Date</th>
							<th>Posted Date</th>
						</tr>';
							
		foreach($voucherRecords as $voucherRecord){
			$ap_voucherRecord = df_get_record('accounts_payable', array('voucher_id'=>$voucherRecord['voucher_id']));
			$childString .= '<tr>' .
								'<td><a href="{$ENV.DATAFACE_SITE_HREF}?-action=list&-table=accounts_payable">' . $voucherRecord['voucher_id'] . '</a></td>' .
								'<td>' . $ap_voucherRecord->strval('voucher_date') . '</td>' .
								'<td>' . $ap_voucherRecord->strval('post_date') . '</td>' .
							'</tr>';
		}
	
		$childString .= '</table>';
		return array(
			'content' => "$childString",
			'class' => 'main',
			'label' => 'Included Vouchers',
			'order' => 10
		);
	}
	
}
?>
