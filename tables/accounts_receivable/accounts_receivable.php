<?php

class tables_accounts_receivable {

//SQL to create VIEW: (SELECT purchase_order_id, assigned_voucher_id, vendor_id FROM `purchase_order_inventory` WHERE assigned_voucher_id IS NULL) UNION (SELECT purchase_order_id, assigned_voucher_id , vendor_id FROM `purchase_order_service` WHERE assigned_voucher_id IS NULL)

	//Permissions
	function getPermissions(&$record){
		//Check if the user is logged in & what their permissions for this table are.
		if( isUser() ){
			$userperms = get_userPerms('accounts_receivable');
			if($userperms == "view")
				return Dataface_PermissionsTool::getRolePermissions("READ ONLY"); //Assign Read Only Permissions
			elseif($userperms == "edit" || $userperms == "post"){
				//Check status, determine if record should be uneditable.
				if ( isset($record) && $record->val('post_status') == 'Posted')
						return Dataface_PermissionsTool::getRolePermissions('NO_EDIT_DELETE');

				//return Dataface_PermissionsTool::getRolePermissions(myRole()); //Assign Permissions based on user Role (typically USER)
				$perms = Dataface_PermissionsTool::getRolePermissions(myRole()); //Assign Permissions based on user Role (typically USER)
					unset($perms['delete']);
				return $perms;
			}
		}

		//Default: No Access
		return Dataface_PermissionsTool::NO_ACCESS();
	}


	
	function getTitle(&$record){
		return "Accounts Receivable Entry for Voucher " . $record->val('voucher_id');
		//$po_type = substr($record->val('purchase_order_id'),0,1);
		//return 'Voucher ID #' . $record->val('voucher_id') . " (" . $record->strval('voucher_date') . ") - Type: " . $po_type . ", Invoice ID: " . $record->val('invoice_id');

		//return 'Invoice ID: ' . $record->val('invoice_id') . ' - Status: ' . $record->val('post_status');
	}

	//function titleColumn(){
	//	return 'CONCAT("Invoice ID: ", invoice_id," - Status: ",post_status)';
	//}	

	
	function account__default(){
		//Get default Accounts Receivable acct
		$default_accounts = df_get_record('_account_defaults', array('default_id'=>1));
		$ar_account = $default_accounts->val('accounts_receivable');
		
		return $ar_account;
	}
	
	function voucher_date__default(){
		return date('Y-m-d');
	}

	function amount__display(&$record){
		if($record->val('amount') != NULL)
			return "$" . $record->val('amount');
			
	}

	
	//Create an Accounts Receivable Entry returns AR Record ID on success, -1 on failure
	public function create_accounts_receivable_entry($invoice_id, $customer_id, $amount, $customer_po){
		//Get default Accounts Receivable acct
		$default_accounts = df_get_record('_account_defaults', array('default_id'=>1));
		$ar_account = $default_accounts->val('accounts_receivable');

		//Create New Record
		$ar_record = new Dataface_Record('accounts_receivable', array());
		$ar_record->setValues(array(
			'invoice_id'=>$invoice_id,
			'customer_id'=>$customer_id,
			'amount'=>$amount,
			'customer_po'=>$customer_po,
			'voucher_date'=>date('Y-m-d'),
			'account'=>$ar_account
		));
		$res = $ar_record->save(null, true);  // checks permissions

		if ( PEAR::isError($res) ){return -1;}
		
		return $ar_record->val('voucher_id');
	}
	

//	function customer_id__display(&$record){
//		if($record->val('customer_id') == NULL)
//			return "---";
			
//		$customer_record = df_get_record('customers', array('customer_id'=>$record->val('customer_id')));
//		return $customer_record->val('customer');
//	}

	function section__accounts(&$record){
		//$app =& Dataface_Application::getInstance(); 
		//$query =& $app->getQuery();
		$childString = '';
			
		$childString .= '<table class="view_add">';
		$childString .= '<tr><th>Account</th><th>Amount</th></tr>';
		
		//$account_records = $record->getRelatedRecords("accounts_receivable_voucher_accounts");
		$account_records = df_get_records_array("accounts_receivable_voucher_accounts",array("voucher_id"=>$record->val("voucher_id")));
		foreach($account_records as $account_record){
			$childString .= '<tr>';
			$childString .= '<td>' . $account_record->display('account_id') . '</td><td style="text-align: right;">$' . $account_record->val('amount') . "</td>";
			$childString .= '</tr>';
		}
		$childString .= '</table>';
		
		
		return array(
			'content' => "$childString",
			'class' => 'main',
			'label' => 'Accounts',
			'order' => 9
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
		//Show Pending button
		elseif(	$record->val('post_status') == ''){
			$childString .= '<form>';
			$childString .= '<input type="hidden" name="-table" value="'.$query['-table'].'">';
			$childString .= '<input type="hidden" name="-action" value="'.$query['-action'].'">';
			$childString .= '<input type="hidden" name="-recordid" value="'.$record->getID().'">';
			
			$childString .= '<input type="hidden" name="-pending" value="'.$record->getID().'">';
			$childString .= '<input type="submit" value="Change Status to: Pending">';

			$childString .= '</form>';
		}
		//If the "Create Credit Voucher" button has been pressed.
/*		elseif(($_GET['-credit'] == $query['-recordid']) && ($query['-recordid'] != "")){
			//Create the Credit Voucher Record
			$new_record = new Dataface_Record('accounts_payable', array());
			$new_record->setValue('voucher_date',date('Y-m-d'));
			$new_record->setValue('invoice_id',$record->val('invoice_id'));
			$new_record->setValue('invoice_date',$record->val('invoice_date'));
			$new_record->setValue('post_status','Pending');
			$new_record->setValue('purchase_order_id',$record->val('purchase_order_id'));
			$new_record->setValue('vendor_id',$record->val('vendor_id'));
			$new_record->setValue('customer_id',$record->val('customer_id'));
			$new_record->setValue('site_id',$record->val('site_id'));
			$new_record->setValue('type',$record->val('type'));
			$new_record->setValue('credit',"Credit for Voucher " . $record->val('voucher_id'));
			$new_record->setValue('description',"Credit to reverse entry for Voucher ID ".$record->val('voucher_id'));
			$new_record->setValue('amount',$record->val('amount'));
			$new_record->setValue('apply_discount',$record->val('apply_discount'));
			$new_record->setValue('modify_discount',$record->val('modify_discount'));
			$new_record->setValue('account_credit',$record->val('account_debit'));
			$new_record->setValue('account_debit',$record->val('account_credit'));
			$res_n = $new_record->save(null, true); //Save Record w/ permission check.

			//Set the check to VOID & Credit to Credit Voucher ID
			if($record->val("check_number") == "")
				$record->setValue('check_number',"VOID");
			else
				$record->setValue('check_number',"VOID - ".$record->val('check_number'));
			$record->setValue('credit',"Credited (".$new_record->val('voucher_id').")");
			$res_r = $record->save(); //Save Record w/ permission check.

			//Unset the assigned Voucher ID in the Purchase Order - so that it can be redone with a new voucher.
				$po_type = $record->val('type');

				//Open record from appropriate table
				if($po_type == 'S'){
					$po_record = df_get_record('purchase_order_service', array('purchase_order_id'=>$record->val('purchase_order_id')));
				}
				elseif($po_type == 'I'){
					$po_record = df_get_record('purchase_order_inventory', array('purchase_order_id'=>$record->val('purchase_order_id')));
				}
				elseif($po_type == 'O'){
					$po_record = df_get_record('purchase_order_office', array('purchase_order_id'=>$record->val('purchase_order_id')));
				}
				elseif($po_type == 'R'){
					$po_record = df_get_record('purchase_order_services_rendered', array('purchase_order_id'=>$record->val('purchase_order_id')));
				}
				else
					return PEAR::raiseError("something went wrong..." . $po_type,DATAFACE_E_NOTICE);
						
				//Set to null
				$po_record->setValue('assigned_voucher_id',NULL);
				$res_p = $po_record->save();


			//Check for errors.
			$errors = 0;
			$msg = "";
			if ( PEAR::isError($res_n)){ //Create new
				$msg .= '<input type="hidden" name="--error" value="Unable to create a Credit Voucher. This is most likely because you do not have the required permissions.<br>">';
				$errors = 1;
			}
			if ( PEAR::isError($res_r)){ //Void check
				$msg .= '<input type="hidden" name="--error" value="Unable to VOID Check. Please see your system administrator to correct this issue.<br>">';
				$errors = 1;
			}
			if ( PEAR::isError($res_p)){ //Unset PO
				$msg .= '<input type="hidden" name="--error" value="Unable to clear the Purchase Order record. The associated Purchase order will not be able to be assigned again until this is fixed. Please see your system administrator to correct this issue.">';
				$errors = 1;
			}
			if($errors == 0)
				$msg = '<input type="hidden" name="--msg" value="Credit Voucher Created.">';
			
			$childString .= '<form name="status_change">';
			$childString .= '<input type="hidden" name="-table" value="'.$query['-table'].'">';
			$childString .= '<input type="hidden" name="-action" value="'.$query['-action'].'">';
			$childString .= '<input type="hidden" name="-recordid" value="'.$record->getID().'">';

			$childString .= $msg;

			$childString .= '</form>';
			$childString .= '<script language="Javascript">document.status_change.submit();</script>';
		}

		//Show Credit Voucher button
		elseif(	$record->val('post_status') == 'Posted' && $record->val('credit') == ""){
			$childString .= '<form>';
			$childString .= '<input type="hidden" name="-table" value="'.$query['-table'].'">';
			$childString .= '<input type="hidden" name="-action" value="'.$query['-action'].'">';
			$childString .= '<input type="hidden" name="-recordid" value="'.$record->getID().'">';
			
			$childString .= '<input type="hidden" name="-credit" value="'.$record->getID().'">';
			$childString .= '<input type="submit" value="Create Credit Voucher">';

			$childString .= '</form>';
		}
*/		//elseif(	$record->val('post_status') == 'Pending'){ //---can do this by linking to -action=ledger_post&selected="this_one"
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


	function accounts__validate(&$record, $value, &$params){
		//Empty the error message
		$params['message'] = '';
		$total_amount = 0;

		//Get rid of the last set in the array - it isn't needed for our use and causes issues
		unset($value['__loaded__']);

		foreach ($value as $account_record){

			//Skip empty lines - do nothing (unless an amount has been assigned, and then return an error)
			if($account_record['account_id'] == ''){
				if($account_record['amount']){ //Case where the 'account' field has been left empty, but an amount has been given
					$params['message'] .= 'Error: An amount has been given, but an account has not been assigned.';
					return false;
				}
			}
			else{
				//Add current amount to total
				$total_amount += $account_record["amount"];
			}
		}

		if($total_amount != $record->val("amount")){
			$params['message'] .= 'Error: The value in "Total Amount" ('.$record->val("amount").') must match the total from the "Accounts" ('.$total_amount.')';
			return false;
		}
		
		//If no errors have occured, move along.
		$params['message'] .= "success!";
		return true;
	}

}
?>
