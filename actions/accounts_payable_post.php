<?php

class actions_accounts_payable_post {
	
	function handle(&$params){

		//Permission check
		if ( !isUser() )
			return Dataface_Error::permissionDenied("You are not logged in");
			
		//If the page has been submitted (Post button pressed), this will be "true", otherwise NULL.
		$confirm = isset($_GET['confirm_post']) ? $_GET['confirm_post'] : null;

		//Get the application instance - used for pulling the query data
		$app =& Dataface_Application::getInstance();

		//Extend the limit of df_get_records_array past the usual first 30 records, and query the records where [post_status == "Pending"]
		$query =& $app->getQuery();
		$query['-skip'] = 0;
		$query['-limit'] = ""; //******I think this will do *all*, but need to double check********
		$query['post_status'] = 'Pending';
		$query['batch_id'] = '=';
			
		//Pull all records with Pending status
		$records = df_get_records_array('accounts_payable', $query);

		//Unset the 'post_status' query
		unset($query['post_status']);
		
		//Set Headers as an empty array
		$headers = array();

		//Run through all pulled records
		foreach($records as $i=>$record){

			//Get basic info
			///$rdate = $record->val('voucher_date');
			$headers[$i]['id'] = $record->val('voucher_id');
			$headers[$i]['date'] = $record->strval('voucher_date');//$rdate['month'].'-'.$rdate['day'].'-'.$rdate['year'];
			$headers[$i]['description'] = $record->val('description');

			//If the Post button has been pressed, process the entries
			if($confirm == "true"){
				//Only modify selected records
				if($_GET[$record->val('voucher_id')]=="on"){
				
					$record->setValue('post_status',"Posted"); //Set status to Posted.
					$record->setValue('post_date',date('Y-m-d')); //Set post date.
					$record->setValue('print_status',"Ready"); //Set post date.
					$res = $record->save(); //Save Record w/o permission check.
					//$res = $record->save(null, true); //Save Record w/ permission check. <-- this is error'ing. Find out why and fix. <-- "status" related permissions

					//Check for errors.
					if ( PEAR::isError($res) ){
						// An error occurred
						$save_error = 1;
						//throw new Exception($res->getMessage());
						break;
					}
					
					//Special tag to denote which entries have been posted.
					$headers[$i]['posted'] = "true";

					//Create Journal Entry
					$journal_entry = array(
										array("account_number"=>$record->val('account_credit'),"credit"=>$record->val('amount')),
										array("account_number"=>$record->val('account_debit'),"debit"=>$record->val('amount'))
										);
					$description = "Accounts Payable Entry, Voucher ID: " . $record->val('voucher_id');
					$res = create_general_ledger_entry($journal_entry, $description);

				}
			}
		}

		//Display the page
		df_display(array("headers"=>$headers,"confirm"=>$confirm), 'accounts_payable_post.html');

	}
}


?>