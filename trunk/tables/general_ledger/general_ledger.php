<?php

class tables_general_ledger {

	function getPermissions(&$record){
		//First check if the user is logged in.
		if( isUser() ){
			//Check status, determine if record should be uneditable.
			if ( isset($record) ){
				if(	$record->val('post_status') == 'Posted')
					return Dataface_PermissionsTool::getRolePermissions('NO_EDIT_DELETE'); //If status:Posted, no editing
				return Dataface_PermissionsTool::getRolePermissions('NO_DELETE'); //No Deleting, ever.
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
			//Check to make sure the account exists (i.e. if the journal line was removed)
			if($journalRecord['account_id'] != 0){
				$ar = df_get_record('chart_of_accounts', array('account_id'=>$journalRecord['account_id']));
				$accountRecord = $ar->vals();
				$childString .= '<tr><td>' . $accountRecord['account_number'] .
								'</td><td>' . $accountRecord['account_name'] .
								'</td><td align="right">' . $journalRecord['debit'] .
								'</td><td align="right">' . $journalRecord['credit'] .
								'</td></tr>';
				$debit_total += $journalRecord['debit'];
				$credit_total += $journalRecord['credit'];
			}
		}
		
		if(round($debit_total,5) == round($credit_total,5))
			$background = 'style="background-color: lightgreen"';
		else
			$background = 'style="background-color: #ff7070"';
		
		$childString .= "<tr><td></td><td></td><td $background align=right><b>".number_format($debit_total,2)."</b></td><td $background align=right><b>".number_format($credit_total,2).'</b></td></tr>';
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
			$childString .= '<input type="hidden" name="-recordid" value="'.$record->getID().'">';

			$childString .= $msg;

			$childString .= '</form>';
			$childString .= '<script language="Javascript">document.status_change.submit();</script>';
		}
		elseif(	$record->val('post_status') == ''){
			$childString .= '<form>';
			$childString .= '<input type="hidden" name="-table" value="'.$query['-table'].'">';
			$childString .= '<input type="hidden" name="-action" value="'.$query['-action'].'">';
			$childString .= '<input type="hidden" name="-recordid" value="'.$record->getID().'">';
			
			$childString .= '<input type="hidden" name="-pending" value="'.$record->getID().'">';
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

	function journal__validate(&$record, $value, &$params){
		//echo "<pre>";
		//print_r($value);
		//echo "</pre>";

		//Empty the error message
		$params['message'] = '';
		
		//Get rid of the last set in the array - it isn't needed for our use and causes issues
		unset($value['__loaded__']);

		//Running total for debit/credit
		$total = 0;
		
		//Run through each journal entry to insure that it is correct
		foreach ($value as $x){
			//Skip empty lines - do nothing (unless a debit/credit has been assigned, and then return an error)
			if($x['account_id'] == ''){
				//Case where the 'item_name' field has been left empty, but a quantity has been given
				if(($x['debit'] || $x['debit'])){
					$params['message'] .= 'CANNOT PROCESS JOURNAL ENTRY: A Debit/Credit has been given, but an Account has not been assigned.';
					return false;
				}
			}
			
			//Process non-empty lines
			else{
				//Case where a user has entered an account, but no further data.
				if($x['account_id'] != '' && !($x['debit'] || $x['credit'])){
					$params['message'] .= 'CANNOT PROCESS JOURNAL ENTRY: An Account has been given, but no Debit/Credit information has been assigned.';
					return false;
				}
			
				//Case where a user has entered data into both the Debit and Credit fields.
				if($x['debit'] && $x['credit']){
					$params['message'] .= 'CANNOT PROCESS JOURNAL ENTRY: Data cannot be entered into both Debit and Credit fields for the same Account.';
					return false;
				}
			
				//Total up debits and credits
				$total += ($x['debit'] - $x['credit']);
			}
		}

		//Case where debits & credits do not match (i.e. total != 0)
		if($total != 0){
			$params['message'] .= 'CANNOT PROCESS JOURNAL ENTRY: The journal entry is out of balance.';
			return false;
		}

		//If no errors have occured, move along.
		return true;
	}
	
	
	
	function beforeSave(&$record){
	}

}

?>