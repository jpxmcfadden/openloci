<?php

class tables_general_ledger_journal {

//	function getPermissions(&$record){
//		$role = "NO_EDIT_DELETE";
//		return Dataface_PermissionsTool::getRolePermissions($role);
//	}
	
	//Set the record title
	function getTitle(&$record){
		$ar = df_get_record('chart_of_accounts', array('account_id'=>$record->val('account_id')));
		return "Ledger Journal ID ".$record->val('ledger_id')." - Acount #".$ar->val('account_number')." (".$ar->val('account_name').")";
	}

	//function titleColumn(){
	//	return 'CONCAT("description")';
	//}
	
/*	function renderRow( &$record ){
		return	'<td>'.$record->display('ledger_id').'</td>'.
				'<td>'.$record->display('date').'</td>'.
				'<td>'.$record->display('account').'</td>'.
				'<td>'.$record->display('debit').'</td>'.
				'<td>'.$record->display('credit').'</td>';
	}
*/

	//Redirect the following fields (used for the relationship from chart_of_accounts) to the related general_ledger record, instead of the general_ledger_journal record.
	function ledger_id__renderCell(&$record){
		return '<a href="index.php?-table=general_ledger&-action=browse&-recordid=general_ledger?ledger_id='.$record->val('ledger_id').'">'.$record->val('ledger_id').'</a>';
	}

	function date__renderCell(&$record){
		$d = $record->val('date');
		return '<a href="index.php?-table=general_ledger&-action=browse&-recordid=general_ledger?ledger_id='.$record->val('ledger_id').'">'.$d['month']."-".$d['day']."-".$d['year'].'</a>';
	}

	function debit__renderCell(&$record){
		return '<a href="index.php?-table=general_ledger&-action=browse&-recordid=general_ledger?ledger_id='.$record->val('ledger_id').'">'.$record->val('debit').'</a>';
	}

	function credit__renderCell(&$record){
		return '<a href="index.php?-table=general_ledger&-action=browse&-recordid=general_ledger?ledger_id='.$record->val('ledger_id').'">'.$record->val('credit').'</a>';
	}

	function beforeSave(&$record){
	
	}


}

?>