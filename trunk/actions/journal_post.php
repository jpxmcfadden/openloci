<?php

class actions_journal_post {
	
	function handle(&$params){

		//Permission check
		if ( !isUser() )
			return Dataface_Error::permissionDenied("You are not logged in");

			
		//If the page has been submitted (Post button pressed), this will be "true", otherwise NULL.
		$confirm = $_GET['confirm_post'];

		//Get the application instance - used for pulling the query data
		$app =& Dataface_Application::getInstance();

		//Extend the limit of df_get_records_array past the usual first 30 records, and query the records where [post_status == "Pending"]
		$query =& $app->getQuery();
		$query['-skip'] = 0;
		$query['-limit'] = ""; //******I think this will do *all*, but need to double check********
		$query['post_status'] = 'Pending';
			
		//Pull all records with Pending status
		$records = df_get_records_array('general_ledger', $query);

		//Unset the 'post_status' query
		unset($query['post_status']);
		
		//Set Headers as an empty array
		$headers = array();

		//Run through all pulled records
		foreach($records as $i=>$record){

			//Get basic info
			$rdate = $record->val('ledger_date');
			$headers[$i]['id'] = $record->val('ledger_id');
			$headers[$i]['date'] = $rdate['month'].'-'.$rdate['day'].'-'.$rdate['year'];
			$headers[$i]['description'] = $record->val('ledger_description');

			//If the Post button has been pressed, process the entries
			if($confirm == "true"){
				//Only modify selected records
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
			}
			else {
				//Get associated journal entries
				$query['ledger_id'] = $record->val('ledger_id');// print_r($query); echo "<br><br>";
				$query['account_id'] = '>0'; //Check to make sure the account exists (i.e. if the journal line was removed)
				$j_records = df_get_records_array('general_ledger_journal', $query);
				//$j_records = df_get_records_array('general_ledger_journal', array('ledger_id'=>$record->val('ledger_id'),'account_id'=>0));

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
		df_display(array("headers"=>$headers,"confirm"=>$confirm), 'journal_post.html');


		


/*			
		//If the post button has *not* been pressed, a.k.a. - On initial page load.
		if(!($_GET['confirm_post'])){
			$app =& Dataface_Application::getInstance();

			//Extend the limit of df_get_records_array past the usual first 30 records, and query the records where [post_status == "Pending"]
			$query =& $app->getQuery();
			$query['-skip'] = 0;
			$query['-limit'] = 10000;
			$query['post_status'] = 'Pending';
			
			//Pull all records with Pending status
			$records = df_get_records_array('general_ledger', $query);

			//Set Headers as an empty array
			$headers = array();

			//Headers for all pulled records
			foreach($records as $i=>$record){

				//Get basic info
				$rdate = $record->val('ledger_date');
				$headers[$i]['id'] = $record->val('ledger_id');
				$headers[$i]['date'] = $rdate['month'].'-'.$rdate['day'].'-'.$rdate['year'];
				$headers[$i]['description'] = $record->val('ledger_description');

				//Get journal entries
				$query['ledger_id'] = $record->val('ledger_id');
				$j_records = df_get_records_array('general_ledger_journal', $query);
				
				$total_debit = 0;
				$total_credit = 0;
				
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

			//Display the page
			df_display(array("headers"=>$headers), 'journal_post.html');
		}
		
		//If the post button has been pressed, post the selected journal entries
		else {
			//Re-Pull all records with Pending status
			$records = df_get_records_array('general_ledger', $query);

			//Set Headers as an empty array
			$headers = array();

			//Process records
			foreach($records as $i=>$record){		
			
				//Only modify selected records
				if($_GET[$record->val('ledger_id')]=="on"){

					$record->setValue('post_status',"Posted"); //Set status to Pending.
					$res = $record->save(); //Save Record w/o permission check.
					//$res = $record->save(null, true); //Save Record w/ permission check. <-- this is error'ing. Find out why and fix.

					//Check for errors.
					if ( PEAR::isError($res) ){
						// An error occurred
						$save_error = 1;
						//throw new Exception($res->getMessage());
						break;
					}

					$rdate = $record->val('ledger_date');
					$headers[$i]['id'] = $record->val('ledger_id');
					$headers[$i]['date'] = $rdate['month'].'-'.$rdate['day'].'-'.$rdate['year'];
					$headers[$i]['description'] = $record->val('ledger_description');

				}
			}
			echo "Errors: ".$save_error;
			//Display the page
			df_display(array("headers"=>$headers,"confirm"=>1), 'journal_post.html');
		}
*/
	}
}


?>