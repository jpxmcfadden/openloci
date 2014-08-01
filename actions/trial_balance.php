<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
class actions_trial_balance {
	
	function handle(&$params){
		$app =& Dataface_Application::getInstance();
		$query =& $app->getQuery();
	
		//Permission check
		if ( !isUser() )
			return Dataface_Error::permissionDenied("You are not logged in");
			//header('Location: index.php?-action=login_prompt'); //Go to dashboard.
		
		$action_section = isset($_GET['action_section']) ? $_GET['action_section'] : NULL;
		$data_array = array();
		
		switch($action_section){
			case NULL: { //Initial State: Select Period based on Period Type
				//Check period_type to make sure it exists and is sane.
				if( isset($_GET['-period_type']) ){
					$period_type = $_GET['-period_type'];
					if($period_type != "month" && $period_type != "quarter" && $period_type != "year"){
						$data_array = array_merge($data_array, array("period_type" => "Unrecognized Period Type"));
						break;
					}
				}
				else{
					$data_array = array_merge($data_array, array("period_type" => "Period Type not Defined"));
					break;
				}

				//Get months with non-closed posted entries
				$records = df_get_records_array("general_ledger", array("post_status"=>"Posted"));
				$dates = array();

				//Parse through all records in the General Ledger with entries that have a Posted status & create a list of options based on this and the selected Period Type (M/Y/Q)
				//$date_str is what will show in the dropdown, $date_rng will be the value passed back for selecting the appropriate records
				foreach($records as $record){
					$record = $record->vals();

					if($period_type == "month"){
						$date_str = date("Y F",strtotime($record['post_date']['year'] . '-' . $record['post_date']['month']));
						$date_rng = ">=" . date("Y-m-1",strtotime($record['post_date']['year'] . '-' . $record['post_date']['month'])) . " AND <=" . date("Y-m-t",strtotime($record['post_date']['year'] . '-' . $record['post_date']['month']));
					}
					elseif($period_type == "year"){
						$date_str = $record['post_date']['year'];
						$date_rng = ">=" . $record['post_date']['year'] . "-01-01 AND <=" . $record['post_date']['year'] . "-12-31";
					}
					elseif($period_type == "quarter"){
						//$quarter = ceil(date('m',strtotime($record['post_date']['year'] . "-" . $record['post_date']['month']))/3);
						$quarter = ceil($record['post_date']['month'] / 3);
						$date_str = $record['post_date']['year'] . ' - Quarter #' . $quarter;
						$date_rng = ">=" . $record['post_date']['year'] . '-' . ((($quarter - 1)*3)+1) . "-01 AND <=" .  date("Y-m-t",strtotime($record['post_date']['year'] . '-' . ($quarter *3) . '-01'));
					}

					//URL Encode $date_rng, so that it can be passed as a $_GET parameter
					$dates[$date_str] = urlencode($date_rng);
				}
					
				$data_array = array_merge($data_array, array("period_type"=>ucfirst($period_type), "period_select"=>$dates));
				
			} break;
			
			case "display": { //Check for unposted batches & show Trial Balance
				//Get & decode the period range
				$date_range = urldecode($_GET['period_range']);
				$data_array = array_merge($data_array, array("period_range"=>$_GET['period_range'])); //Add period_range - going to need to use it again after confim button is pressed.

				//Pull all records in the period
				$records = df_get_records_array("general_ledger", array("post_date"=>$date_range));

				//Check for unposted batches in the period
				$unposted_records = array(); //Create empty array
				foreach($records as $record){ //Cycle through records to check for unposted ones, add to array
					if($record->val('post_status') != "Posted"){
						$unposted_records = array_merge($unposted_records,array($record->vals()));
					}
				}
				
				//If unposted batches, show them with error message
				if(count($unposted_records) > 0){
					$data_array = array_merge($data_array, array("unposted"=>$unposted_records));
					break;
				}

				//If no unposted batches - Show / print Trial Balance, Income Statement & Balance Sheets
				$account_data = array();
				$total_debit = 0;
				$total_credit = 0;
				foreach($records as $record){
					$journal_entries = df_get_records_array("general_ledger_journal", array("ledger_id" => $record->val("ledger_id")));
					//foreach($journal_entries as $journal_entry){ //Initialize offsets/indexes - i.e. get rid of "notice"s - can get rid of this
					//	$account_data[$journal_entry->val('account_id')] = "";
					//	$account_data[$journal_entry->val('account_id')]["credit"] = 0;
					//	$account_data[$journal_entry->val('account_id')]["debit"] = 0;
					//}
					foreach($journal_entries as $journal_entry){
						$account_data[$journal_entry->val('account_id')]['debit'] += $journal_entry->val('debit');
						$account_data[$journal_entry->val('account_id')]['credit'] += $journal_entry->val('credit');
						$total_debit += $journal_entry->val('debit');
						$total_credit += $journal_entry->val('credit');
					}
				}
				
				//Add account information to $account_data
				foreach($account_data as $account_id => $account){
					$account_record = df_get_record("chart_of_accounts", array("account_id" => $account_id));
					$account_data[$account_id]["account_number"] = $account_record->val("account_number");
					$account_data[$account_id]["account_name"] = $account_record->val("account_name");
					$account_data[$account_id]["debit"] = number_format($account_data[$account_id]["debit"],2); //Convert to 2 decimals
					$account_data[$account_id]["credit"] = number_format($account_data[$account_id]["credit"],2); //Convert to 2 decimals
				}
				$total_debit = number_format($total_debit, 2);  //Convert to 2 decimals
				$total_credit = number_format($total_credit, 2);  //Convert to 2 decimals

				//Sort by Account Number in ascending order
				$this->csort($account_data, 'account_number');
				
				$data_array = array_merge($data_array, array("accounts"=>$account_data,"total_debit"=>$total_debit,"total_credit"=>$total_credit)); //Add account data & credit/debit totals
			
			} break;

			case "confirm": {
				//Change Status to "Closed"
				//Create entry in table status		
			
			} break;
			
			default: {
				echo "SOMETHING BROKE!"; //use msg paramater
			}
		}

		//echo "<pre>"; print_r($data_array); echo "</pre>";
		df_display($data_array, 'close_ledger.html');		
		return 0;				
	}
	
	//Sort array ($arr) by the given column ($col). Default sort: Ascending.
	function csort(&$arr, $col, $dir = SORT_ASC) {
		$sort_col = array();
		foreach ($arr as $key=> $row) {
			$sort_col[$key] = $row[$col];
		}

		array_multisort($sort_col, $dir, $arr);
	}
}


?>