<?php

class tables_chart_of_accounts_categories {

	function getPermissions(&$record){
		$role = "NO_EDIT_DELETE";
		return Dataface_PermissionsTool::getRolePermissions($role);
	}
	
	function beforeInsert(&$record){
		$act_type = $record->val('account_type');
		$sql_query = "SELECT MAX(category_number) as m_c_n FROM `chart_of_accounts_categories` WHERE account_type = '$act_type'";
		$max_cat_number_query = mysql_query($sql_query, df_db());
		$max_cat_number_record = mysql_fetch_array($max_cat_number_query);
		$max_cat_number = $max_cat_number_record['m_c_n'];

		if(max_cat_number >= 999)
			return PEAR::raiseError("Cannot Create New Category. Maximum number of account type categories (999) has been exceeded.",DATAFACE_E_NOTICE);

		$record->setValue('category_number',$max_cat_number+1);
		
		//return PEAR::raiseError("FIN... ".$record->val('account_type')." ".$record->val('category_number'),DATAFACE_E_NOTICE);
 	}
	
}

?>
