<?php

class tables_chart_of_accounts {


	//Permissions
	function getPermissions(&$record){
		//Check if the user is logged in & what their permissions for this table are.
		if( isUser() ){
			$userperms = get_userPerms('chart_of_accounts');
			if($userperms == "view")
				return Dataface_PermissionsTool::getRolePermissions("READ ONLY"); //Assign Read Only Permissions
			elseif($userperms == "edit"){
				$perms = Dataface_PermissionsTool::getRolePermissions(myRole()); //Assign Permissions based on user Role (typically USER)
				unset($perms['delete']);
				return $perms;
			}
		}

		//Default: No Access
		return Dataface_PermissionsTool::NO_ACCESS();
	}

	//Remove the "edit" tab. Field permissions are set to 'edit'=>0 anyway, but since changing "account_status" required general edit access via getPermissions(), which then automatically shows the tab - this needs to be visually disabled.
	function init(){
		$app =& Dataface_Application::getInstance();
		$query =& $app->getQuery();
		
		//Only on the 'view' page. Otherwise, causes issues with looking at the entire table (i.e. user sees a blank page).
		if($query['-action'] == 'view')
			echo "<style>#record-tabs-edit{display: none;}</style";
	}

	function __field__permissions(&$record){
		//Set all fields as non-editable
		return array('edit'=>0);
	}
	
	function account_status__permissions(&$record){
		//Check permissions & if allowed, set edit permissions for "account_status"
		if(get_userPerms('chart_of_accounts') == "edit")
			return array('edit'=>1);
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


	function section__status(&$record){
		//Check permissions & if allowed, show Change Status button
		if(get_userPerms('chart_of_accounts') == "edit"){

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
				//$res = $record->save();//(null, true); //Save Record w/o permission check. - This is because the default permissions are set to not allow changes. - For the future modify field permissions.
				$res = $record->save(null, true); //Save Record w/ permission check.
				
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
	}





	
	function beforeInsert(&$record){
		
		//Convert Account Type to appropriate account number prefix
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
		
		//Get Account Type and Category
		$act_type = $record->val('account_type');
		$act_cat = $record->val('account_category');

		//Get the last id entered for the selected Account Type and Category
		$sql_query = "SELECT MAX(account_id) as m_a_id FROM `chart_of_accounts`WHERE (account_type='$act_type' AND account_category='$act_cat')";
		$max_account_id_query = mysql_query($sql_query, df_db());
		$max_account_id_record = mysql_fetch_array($max_account_id_query);
		$max_account_id = $max_account_id_record['m_a_id'];

		//Get the category record
		$category_record = df_get_record('chart_of_accounts_categories', array('category_id'=>$act_cat));

		//If an id for selected Account Type and Category already exists take the last 5 digits (unique acct #) and +1
		if(isset($max_account_id)){
			$act_id_record = df_get_record('chart_of_accounts', array('account_id'=>$max_account_id));
			$act_n_unique = substr($act_id_record->val('account_number'),-5,5)+1;
			//Make sue that the unique number is less than the max allotted (99999)
			if($act_n_unique > 99999)
				return PEAR::raiseError("Cannot Create New Account. Maximum number of accounts for account category: ".$category_record->val('category_name')." (99999) has been exceeded.",DATAFACE_E_NOTICE);
		}
		//Else set unique acct # to 1
		else{
			$act_n_unique = 1;
		}
		
		//Format the Account Number: -_---_----- == (acct_type (1) - category (3) - unique (5))
		$act_n = $acct_prefix."-".sprintf("%03d",$category_record->val('category_number'))."-".sprintf("%05d",$act_n_unique);

		//Set the new Account Number
		$record->setValue('account_number',$act_n);
		
		//Set Status to Active
		$record->setValue('account_status','Active');
 
		//return PEAR::raiseError("FIN",DATAFACE_E_NOTICE);
	}
	
}

?>
