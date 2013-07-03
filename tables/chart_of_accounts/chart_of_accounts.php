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
	
	function account_status__default(){
		return "Active";
	}
	
	function beforeInsert(&$record){
		switch($record->val('account_type')){
			case "AST":
				$acct_prefix = 1;
				break;
			case "LIB":
				$acct_prefix = 2;
				break;
			case "EQI":
				$acct_prefix = 3;
				break;
			case "REV":
				$acct_prefix = 4;
				break;
			case "CGS":
				$acct_prefix = 5;
				break;
			case "EXP":
				$acct_prefix = 6;
				break;
		}
		
		$sql_query = "SELECT MAX(account_number) as m_a_n FROM `chart_of_accounts` WHERE account_number LIKE '$acct_prefix%'";
		$max_account_number_query = mysql_query($sql_query, df_db());
		$max_account_number_record = mysql_fetch_array($max_account_number_query);
		$max_account_number = $max_account_number_record['m_a_n'];
		
		$record->setValue('account_number',$max_account_number+1);
		$record->setValue('account_status','Active');
 
		//echo $record->val('account_number');
		//return PEAR::raiseError("FIN",DATAFACE_E_NOTICE);
	}
	
}

?>