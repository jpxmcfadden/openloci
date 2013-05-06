<?php

class tables_general_ledger {

	function getPermissions(&$record){
		//First check if the user is logged in.
		if( isUser() ){
			//Check status, determine if record should be uneditable.
			if ( isset($record) ){
				if(	$record->val('post_status') == 'Posted')
					return Dataface_PermissionsTool::getRolePermissions('NO_EDIT_DELETE');
			}
		}
		else
			return Dataface_PermissionsTool::NO_ACCESS();
	}



	//Set the record title
	function getTitle(&$record){
		return $record->val('ledger_description');
	}

	function titleColumn(){
		return 'CONCAT("ledger_description")';
	}
	
	function ledger_date__default() {
       return date('Y-m-d');
	}

	
	function section__journal(&$record){

		$debit_total = 0;
		$credit_total = 0;
		$childString = "";
		
		$childString .= '<table class="view_add"><tr>
							<th>Account Number</th>
							<th>Account Name</th>
							<th>Debit</th>
							<th>Credit</th>
						</tr>';

		$jr = $record->getRelatedRecords('general_ledger_journal');
		foreach ($jr as $journalRecord){
			$ar = df_get_record('chart_of_accounts', array('account_id'=>$journalRecord['account_id']));
			$accountRecord = $ar->vals();
			$childString .= '<tr><td>' . $accountRecord['account_number'] .
							'</td><td>' . $accountRecord['account_name'] .
							'</td><td>' . $journalRecord['debit'] .
							'</td><td>' . $journalRecord['credit'] .
							'</td></tr>';
			$debit_total += $journalRecord['debit'];
			$credit_total += $journalRecord['credit'];
		}
			if($debit_total == $credit_total)
				$background = 'style="background-color: lightgreen"';
			else
				$background = 'style="background-color: #ff7070"';
		
			$childString .= "<tr><td></td><td></td><td $background><b>".number_format($debit_total,2)."</b></td><td $background><b>".number_format($credit_total,2).'</b></td></tr>';
			$childString .= '</table><br>';

		
		
		return array(
			'content' => "$childString",
			'class' => 'main',
			'label' => 'Journal Entries',
			'order' => 10
		);
	}
	
	function section__status(&$record){
		$app =& Dataface_Application::getInstance(); 
		$query =& $app->getQuery();

		$childString = '';

		//If the "Change Status To: Pending" button has been pressed.
		//Because both the $_GET and $query will be "" on a new record, check to insure that they are not empty.
		if(($_GET['-pending'] == $query['-recordid']) && ($query['-recordid'] != "")){
			$record->setValue('post_status',"Pending"); //Set status to Pending.
			$res = $record->save(null, true); //Save Record w/ permission check.

			//Check for errors.
			if ( PEAR::isError($res) ){
				// An error occurred
				//throw new Exception($res->getMessage());
				$msg = '<input type="hidden" name="--error" value="Unable to change status. This is most likely because you do not have the required permissions.">';
			}
			else
				$msg = '<input type="hidden" name="--msg" value="Status Changed to: Pending">';
			
			$childString .= '<form name="status_change">';
			$childString .= '<input type="hidden" name="-table" value="'.$query['-table'].'">';
			$childString .= '<input type="hidden" name="-action" value="'.$query['-action'].'">';
			$childString .= '<input type="hidden" name="-recordid" value="'.$query['-recordid'].'">';

			$childString .= $msg;

			$childString .= '</form>';
			$childString .= '<script language="Javascript">document.status_change.submit();</script>';
		}
		elseif(	$record->val('post_status') == ''){
			$childString .= '<form>';
			$childString .= '<input type="hidden" name="-table" value="'.$query['-table'].'">';
			$childString .= '<input type="hidden" name="-action" value="'.$query['-action'].'">';
			$childString .= '<input type="hidden" name="-recordid" value="'.$query['-recordid'].'">';

			$childString .= '<input type="hidden" name="-pending" value="'.$query['-recordid'].'">';
			$childString .= '<input type="submit" value="Change Status to: Pending">';

			$childString .= '</form>';
		}
		//elseif(	$record->val('post_status') == 'Pending'){ //---can do this by linking to -action=ledger_post&selected="this_one"
		//	$childString .= 'Post';
		//}
		else {
			$childString .= "No further options available";
		}
		
		//if(	$record->val('post_status') == '')
		return array(
			'content' => "$childString",
			'class' => 'main',
			'label' => 'Change Status',
			'order' => 10
		);
	}

	
	function beforeSave(&$record){
	}

}

?>