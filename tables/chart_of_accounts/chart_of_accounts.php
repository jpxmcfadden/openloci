<?php

class tables_chart_of_accounts {

	function getPermissions(&$record){
		$role = "NO_EDIT_DELETE";
		return Dataface_PermissionsTool::getRolePermissions($role);
	}
	
	//function account_status__permissions(&$record){
	//	$perms = Dataface_PermissionsTool::NO_ACCESS();
		//$perms = array_merge($perms, Dataface_PermissionsTool::getRolePermissions(myRole()));
		//$perms['view']=1;
		//$perms['edit']=1;
		//print_r($perms);
	//	$perms = Dataface_PermissionsTool::getRolePermissions("ACCESS");

	//	return $perms;
	//}
	

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


	function section__status(&$record){
		$app =& Dataface_Application::getInstance(); 
		$query =& $app->getQuery();
		$childString = '';

		//If the "Change Status" button has been pressed.
		//Because both the $_GET and $query will be "" on a new record, check to insure that they are not empty.
		if(($_GET['-status_change'] == $query['-recordid']) && ($query['-recordid'] != "")){
			if($record->val('account_status') == "Active")
				$record->setValue('account_status',"Inactive"); //Set status to Inactive.
			else
				$record->setValue('account_status',"Active"); //Set status to Active.
			$res = $record->save();//(null, true); //Save Record w/o permission check. - This is because the default permissions are set to not allow changes. - For the future modify field permissions.
			
			//Check for errors.
			if ( PEAR::isError($res) ){
				// An error occurred
				//throw new Exception($res->getMessage());
				$msg = '<input type="hidden" name="--error" value="Unable to change status. This is most likely because you do not have the required permissions.">';
			}
			else {
				if($record->val('account_status') == "Active")
					$msg = '<input type="hidden" name="--msg" value="Account Status Changed to: Active">';
				elseif($record->val('account_status') == "Inactive")
					$msg = '<input type="hidden" name="--msg" value="Account Status Changed to: Inactive">';
				else 
					$msg = '<input type="hidden" name="--error" value="Something Broke: Status='.$record->val('account_status').'">';
			}
			
			$childString .= '<form name="status_change">';
			$childString .= '<input type="hidden" name="-table" value="'.$query['-table'].'">';
			$childString .= '<input type="hidden" name="-action" value="'.$query['-action'].'">';
			$childString .= '<input type="hidden" name="-recordid" value="'.$record->getID().'">';

			$childString .= $msg;

			$childString .= '</form>';
			$childString .= '<script language="Javascript">document.status_change.submit();</script>';
		}
		else{
			$childString .= '<form>';
			$childString .= '<input type="hidden" name="-table" value="'.$query['-table'].'">';
			$childString .= '<input type="hidden" name="-action" value="'.$query['-action'].'">';
			$childString .= '<input type="hidden" name="-recordid" value="'.$record->getID().'">';
			
			$childString .= '<input type="hidden" name="-status_change" value="'.$record->getID().'">';

			if($record->val('account_status') == "Active")
				$childString .= '<input type="submit" value="Change Account Status to: Inactive">';
			elseif($record->val('account_status') == "Inactive")
				$childString .= '<input type="submit" value="Change Account Status to: Active">';

			$childString .= '</form>';
		}
		//elseif(	$record->val('post_status') == 'Pending'){ //---can do this by linking to -action=ledger_post&selected="this_one"
		//	$childString .= 'Post';
		//}
		//else {
		//	$childString .= "No further options available";
		//}
		
		//if(	$record->val('post_status') == '')
		return array(
			'content' => "$childString",
			'class' => 'main',
			'label' => 'Change Status',
			'order' => 10
		);
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