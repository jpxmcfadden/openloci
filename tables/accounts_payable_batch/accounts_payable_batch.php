<?php

class tables_accounts_payable_batch {

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

}
?>
