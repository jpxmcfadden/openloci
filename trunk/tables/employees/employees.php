<?php

class tables_employees {

	//Permissions
	function getPermissions(&$record){
		//Check if the user is logged in & what their permissions for this table are.
		if( isUser() ){
			$userperms = get_userPerms('employees');
			if($userperms == "view_techs"){
				//Allow access to only those employee records where the employee is assigned as a technician.
				$employee_table =& Dataface_Table::loadTable('employees');
				$employee_table->setSecurityFilter(array('tech'=>'Y'));
				return Dataface_PermissionsTool::getRolePermissions("READ ONLY"); //Assign Read Only Permissions
			}
			if($userperms == "view_all")
				return Dataface_PermissionsTool::getRolePermissions("READ ONLY"); //Assign Read Only Permissions
			elseif($userperms == "edit")
				return Dataface_PermissionsTool::getRolePermissions(myRole()); //Assign Permissions based on user Role (typically USER)
		}

		//Default: No Access
		return Dataface_PermissionsTool::NO_ACCESS();
	}

	//Deny view permissions to all fields for non 'edit' users & re-enable the specific fields that the user is entitled to see
	function __field__permissions($record){
		//Check Permissions
		$userperms = get_userPerms('employees');
		if ( isset($record) && ($userperms == "view_techs" || $userperms == "view_all") )
			return array('view'=>0);
	}

	//Viewable for "view_techs" && "view_all" permissions
		function employee_id__permissions(&$record){
			//Check permissions & if allowed, set edit permissions to be viewable
			$userperms = get_userPerms('employees');
			if ( isset($record) && ($userperms == "view_techs" || $userperms == "view_all") )
				return array('view'=>1);
		}
		function first_name__permissions(&$record){
			//Check permissions & if allowed, set edit permissions to be viewable
			$userperms = get_userPerms('employees');
			if ( isset($record) && ($userperms == "view_techs" || $userperms == "view_all") )
				return array('view'=>1);
		}
		function last_name__permissions(&$record){
			//Check permissions & if allowed, set edit permissions to be viewable
			$userperms = get_userPerms('employees');
			if ( isset($record) && ($userperms == "view_techs" || $userperms == "view_all") )
				return array('view'=>1);
		}

	//Viewable for "view_all" permissions
		function address__permissions(&$record){
			//Check permissions & if allowed, set edit permissions to be viewable
			$userperms = get_userPerms('employees');
			if ( isset($record) && $userperms == "view_all" )
				return array('view'=>1);
		}
		function city__permissions(&$record){
			//Check permissions & if allowed, set edit permissions to be viewable
			$userperms = get_userPerms('employees');
			if ( isset($record) && $userperms == "view_all" )
				return array('view'=>1);
		}
		function state__permissions(&$record){
			//Check permissions & if allowed, set edit permissions to be viewable
			$userperms = get_userPerms('employees');
			if ( isset($record) && $userperms == "view_all" )
				return array('view'=>1);
		}
		function zip__permissions(&$record){
			//Check permissions & if allowed, set edit permissions to be viewable
			$userperms = get_userPerms('employees');
			if ( isset($record) && $userperms == "view_all" )
				return array('view'=>1);
		}
		function phone1__permissions(&$record){
			//Check permissions & if allowed, set edit permissions to be viewable
			$userperms = get_userPerms('employees');
			if ( isset($record) && $userperms == "view_all" )
				return array('view'=>1);
		}
		function phone2__permissions(&$record){
			//Check permissions & if allowed, set edit permissions to be viewable
			$userperms = get_userPerms('employees');
			if ( isset($record) && $userperms == "view_all" )
				return array('view'=>1);
		}
		function email__permissions(&$record){
			//Check permissions & if allowed, set edit permissions to be viewable
			$userperms = get_userPerms('employees');
			if ( isset($record) && $userperms == "view_all" )
				return array('view'=>1);
		}

	
	//Set timelog edit permissions to use the timelog table's permission settings (unset related record permissions)
	function rel_time_logs__permissions(&$record){
		$perms = &Dataface_PermissionsTool::getRolePermissions(myRole());
		unset($perms['edit related records']);
		unset($perms['delete related record']);
	}


	function getTitle(&$record){
		return $record->val('first_name').' '.$record->val('last_name');
	}

	function titleColumn(){
		return 'CONCAT(last_name,", ",first_name)';
	}

	function email__htmlValue(&$record){
		return '<a href="mailto:' . $record->strval('email') . '">' . $record->strval('email') . '</a>'; 
	}
	

	//DEFAULT VALUES
	function state__default(){
		return default_location_state();
	}

//	function regular_hours__default(){
//		return "40.00";
//	}

	function full_part__default(){
		return "FT";
	}

//	function pay_period__default(){
//		return "WK";
//	}

	function direct_deposit__default(){
		return "N";
	}

	function exemptions_federal__default(){
		return "0";
	}

	function exemptions_state__default(){
		return "0";
	}

	function exemptions_city__default(){
		return "0";
	}

	function active__default(){
		return "Y";
	}
	
	//Validate that the total from assigned wage accounts is 100%
	function wage_accounts__validate( &$record, $value, &$params=array()){
		//Unassign the "__loaded__" entry from the $value array
		unset($value["__loaded__"]);

		//Assign current percent to 0.
		$total_percent = 0;
		
		//Parse through all the data in $value
		foreach($value as $wage_account){
			//If an amount has been assigned (i.e. not the last "blank" entry)
			if(isset($wage_account['amount_percent']) && $wage_account['amount_percent'] != null){
				//If an account has not been assigned, but an amount has - immediately fail.
				if($wage_account['account_id'] == null || $wage_account['account_id'] == 0){ //Added the || == 0 as a failsafe, in case somehow the account gets assigned to null (which will store as 0 in the database, since the field is 'not null')
					$params['message'] = "An account has been left blank.";
					return false;
				}
				
				$total_percent += $wage_account["amount_percent"];
				
			}
		}
		
		if($total_percent != 100){
			$params['message'] = "The total percent assigned for wage accounts does not equal 100%.";
			return false;
		}
		
		return true;
	}
	
	function beforeSave(&$record){
/*		$msg = "Message";

		$wage_accounts = df_get_related_records('wage_accounts');
		$msg = isset($wage_accounts);
		foreach($wage_accounts as $wage_account){
			$msg .= "x";
		//	$msg .= $wage_account->val("amount_percent") . " ** <br>";
		}
	
	
	
	
		header('Location: '.$record->getURL('-action=edit').'&--msg='.urlencode($msg)); //Reload the page so that the fields update.
		return PEAR::raiseError('',DATAFACE_E_NOTICE); //Return an error and don't save the record.;
*/	}

	function afterSave(&$record)
	{
	}

	//function section__more(&$record){
	//	return array(
	//		'content' => '',
	//		'class' => 'main',
	//		'label' => 'More Details',
	//		'order' => 2
	//	);
	//}

	//function field__timestamp(&$record){
	//function address__renderCell(&$record){
	//	return '<div style="white-space:nowrap">'.$record->strval('address').'</div>';
	//}
	//function email__renderCell( &$record ){
	//	return $record->strval('email').' ( send email)';
	//}

	
}
?>