<?php

class actions_accounts_payable_batch_post {
	
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
		$query['post_status'] = '=';
		
		$batch_records = df_get_records_array('accounts_payable_batch', $query);

		//Unset the 'post_status' query
		unset($query['post_status']);

		//Set Headers as an empty array
		$headers = array();
		
		foreach($batch_records as $batch){
			$batch_id = $batch->val('batch_id');
			//Pull all records in the batch
			$query['batch_id'] = $batch->val('batch_id');
			$records = df_get_records_array('accounts_payable', $query);

			//If the Post button has been pressed, process the entries
			if($confirm == "true"){
				//Only modify selected records
				if($_GET[$batch->val('batch_id')]=="on"){
					
					$batch->setValue('post_status',"Posted"); //Set status to Posted.
					$batch->setValue('post_date',date('Y-m-d')); //Set post date.
					//$res = $record->save(); //Save Record w/o permission check.
					$res = $batch->save(null, true); //Save Record w/ permission check. <-- this is error'ing. Find out why and fix. <-- "status" related permissions
			
					//Check for errors.
					if ( PEAR::isError($res) ){
						// An error occurred
						$save_error = 1;
						//throw new Exception($res->getMessage());
						break;
					}
					
					//Empty the Journal Data
					$journal_data = array();
										
					//Run through all pulled records
					foreach($records as $i=>$record){

						//Get basic info
						///$rdate = $record->val('voucher_date');
						$headers[$batch_id][$i]['id'] = $record->val('voucher_id');
						$headers[$batch_id][$i]['date'] = $record->strval('voucher_date');//$rdate['month'].'-'.$rdate['day'].'-'.$rdate['year'];
						$headers[$batch_id][$i]['description'] = $record->val('description');
													
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

						//Create Journal Entry
						$journal_data[$record->val('account_credit')]['credit'] += $record->val('amount');
						$journal_data[$record->val('account_credit')]['account_number'] += $record->val('account_credit');
						$journal_data[$record->val('account_debit')]['debit'] += $record->val('amount');
						$journal_data[$record->val('account_debit')]['account_number'] += $record->val('account_debit');
					}
				}
			
				$description = "Accounts Payable Batch Entry, Batch ID: " . $batch_id;
//				$res = create_general_ledger_entry($journal_data, $description);

			}
			//If the Post button has not yet been pressed (first load)
			else{
				//Run through all pulled records
				foreach($records as $i=>$record){

					//Get basic info
					///$rdate = $record->val('voucher_date');
					$headers[$batch_id][$i]['id'] = $record->val('voucher_id');
					$headers[$batch_id][$i]['date'] = $record->strval('voucher_date');//$rdate['month'].'-'.$rdate['day'].'-'.$rdate['year'];
					$headers[$batch_id][$i]['description'] = $record->val('description');
					
				}
			}
		}

		//Display the page
		df_display(array("headers"=>$headers,"confirm"=>$confirm), 'accounts_payable_batch_post.html');

	}
}


?>