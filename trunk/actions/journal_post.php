<?php

class actions_journal_post {
	
	function handle(&$params){

		//Permission check
		if ( !isUser() )
			return Dataface_Error::permissionDenied("You are not logged in");
		
		//If the post button has been pressed, post the selected journal entries
		if($_GET['confirm_post']){

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
		else{

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
	}
}


?>