<?php

class actions_journal_recurring {
	
	function handle(&$params){

		//Permission check
		if ( !isUser() )
			return Dataface_Error::permissionDenied("You are not logged in");

			
		//If the page has been submitted (Apply button pressed), this will be "true", otherwise NULL.
		$confirm = $_GET['confirm'];

		//Get the application instance - used for pulling the query data
		$app =& Dataface_Application::getInstance();

		//Extend the limit of df_get_records_array past the usual first 30 records, and query the records where [post_status == "Pending"]
		$query =& $app->getQuery();
		$query['-skip'] = 0;
		$query['-limit'] = ""; //******I think this will do *all*, but need to double check********
		$query['status'] = 'ACT';
			
		//Pull all records with Pending status
		$records = df_get_records_array('general_ledger_recurring', $query);

		//Unset the 'status' query
		unset($query['status']);
		
		//Set Headers as an empty array
		$headers = array();

		//Run through all pulled records
		foreach($records as $i=>$record){

			//Get basic info
			$rdate = date('m-d-Y');
			//$headers[$i]['id'] = $record->val('ledger_id');
			$headers[$i]['date'] = $rdate;
			$headers[$i]['description'] = $record->val('recurring_ledger_description');

			//If the Apply button has been pressed, process the entries
			if($confirm == "true"){
				//Create New Ledger Record
				$gl_record = new Dataface_Record('general_ledger', array());

				//Set Ledger Record Values
				$gl_record->setValues(
									array(
										'ledger_date'=>date('Y-m-d'),
										'ledger_description'=>$record->val('recurring_ledger_description'),
										'post_status'=>'Pending'
									)
								);
			
				//Save Ledger Record
				$res = $gl_record->save(); //Save Record w/o permission check.
				//$res = $gl_record->save(null, true); //Save Record w/ permission check. <-- this is error'ing. Find out why and fix.

				//Check for Errors!!
				//If ($res...)
			
				//Now create the appropriate "journal" entries
				//Get associated journal entries
				$query['recurring_ledger_id'] = $record->val('recurring_ledger_id');// print_r($query); echo "<br><br>";
				$query['account_id'] = '>0'; //Check to make sure the account exists (i.e. if the journal line was removed)
				$j_records = df_get_records_array('general_ledger_recurring_journal', $query);

				//Process all the journal entries in the ledger journal
				foreach($j_records as $j=>$j_record){
					//Create New Journal Record
					$glj_record = new Dataface_Record('general_ledger_journal', array());

					//Set Journal Record Values
					$glj_record->setValues(
										array(
											'ledger_id'=>$gl_record->val('ledger_id'),
											'date'=>$gl_record->val('ledger_date'),
											'account_id'=>$j_record->val('account_id'),
											'debit'=>$j_record->val('debit'),
											'credit'=>$j_record->val('credit')
										)
									);
			
					//Save Ledger Record
					$res = $glj_record->save(); //Save Record w/o permission check.
					//$res = $gl_record->save(null, true); //Save Record w/ permission check. <-- this is error'ing. Find out why and fix.
					//Check for Errors!!
					//If ($res...)
				}


				
			/*	//Only modify selected records
				if($_GET[$record->val('ledger_id')]=="on"){
				
					$record->setValue('post_status',"Posted"); //Set status to Pending.
					$record->setValue('post_date',date('Y-m-d')); //Set post date.
					$res = $record->save(); //Save Record w/o permission check.
					//$res = $record->save(null, true); //Save Record w/ permission check. <-- this is error'ing. Find out why and fix.

					//Check for errors.
					if ( PEAR::isError($res) ){
						// An error occurred
						$save_error = 1;
						//throw new Exception($res->getMessage());
						echo "Error occurred trying to save record: ".$res->getMessage();
						break;
					}
					
					//Special tag to denote which entries have been posted.
					$headers[$i]['posted'] = "true";
					
					//Get associated journal entries
					$query['ledger_id'] = $record->val('ledger_id');
					$query['account_id'] = '>0'; //Check to make sure the account exists (i.e. if the journal line was removed)
					$j_records = df_get_records_array('general_ledger_journal', $query);

					//Process all the journal entries in the ledger journal
					foreach($j_records as $j=>$j_record){
						$coa_record = df_get_record('chart_of_accounts', array('account_id'=>$j_record->val('account_id')));
						
						//Do a check to see what kind of account
						//Assets/Expense (contra-equity/contra-liability)	->	total = sum(credit) - sum(debit) 
						//Liablility/Equity/Revenue (contra-asset) ->	total = sum(debit) - sum(credit)
						//	AST = Assets
						//	LIB = Liability
						//	EQI = Equity
						//	REV = Revenue
						//	EXP = Expense
						
						switch ($coa_record->val('account_type')){
							case "AST":
							case "EXP":
								$total = $j_record->val('debit') - $j_record->val('credit');
								break;
							case "LIB":
							case "EQI":
							case "REV":
								$total = $j_record->val('credit') - $j_record->val('debit');
								break;
						}
						
						//save
						$coa_record->setValue('account_balance',$coa_record->val('account_balance')+$total); //Set status to Pending.
						$res = $coa_record->save(); //Save Record w/o permission check.
						
						//CHECK FOR ERRORS*****

					}

				}
			*/}
			else {
				//Get associated journal entries
				$query['recurring_ledger_id'] = $record->val('recurring_ledger_id');// print_r($query); echo "<br><br>";
				$query['account_id'] = '>0'; //Check to make sure the account exists (i.e. if the journal line was removed)
				$j_records = df_get_records_array('general_ledger_recurring_journal', $query);

				//Set the total debit/credit variables to 0.
				$total_debit = 0;
				$total_credit = 0;
				
				//Process all the journal entries in the ledger journal
				foreach($j_records as $j=>$j_record){
						$coa_record = df_get_record('chart_of_accounts', array('account_id'=>$j_record->val('account_id')));
							
						$headers[$i]['entries'][$j]['account']=$coa_record->val('account_name');
						$headers[$i]['entries'][$j]['debit']=$j_record->val('debit');
						$headers[$i]['entries'][$j]['credit']=$j_record->val('credit');
							
						$total_debit += $j_record->val('debit');
						$total_credit += $j_record->val('credit');
				}

				$headers[$i]['total_debit'] = number_format($total_debit,2);
				$headers[$i]['total_credit'] = number_format($total_credit,2);
			}
		}
			
		//Display the page
		df_display(array("headers"=>$headers,"confirm"=>$confirm), 'journal_recurring.html');
	}
}


?>