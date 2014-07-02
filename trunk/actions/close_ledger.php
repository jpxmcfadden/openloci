<?php

class actions_close_ledger {
	
	function handle(&$params){
		$app =& Dataface_Application::getInstance();
		$query =& $app->getQuery();
	
		//Permission check
		if ( !isUser() )
			return Dataface_Error::permissionDenied("You are not logged in");
			//header('Location: index.php?-action=login_prompt'); //Go to dashboard.
			
		//Period type check
		if( isset($_GET['-period_type']) ){
			$period_type = $_GET['-period_type'];
			if($period_type != "month" && $period_type != "quarter" && $period_type != "year"){
				df_display(array("period_type" => "Unrecognized Period Type"), 'close_ledger.html');
				return 0;
			}
		}
		else{
			df_display(array("period_type" => "Period Type not Defined"), 'close_ledger.html');
			return 0;
		}

		//If Period range has not been selected -> 1) Get months with non-closed posted entries
		if(!isset($_GET['period_range'])){
			$records = df_get_records_array("general_ledger", array("ledger_status"=>"Posted"));
			$dates = array();
			foreach($records as $record){
				$record = $record->vals();
				
				if($period_type == "month")
					$date_str = date("Y F",strtotime($record['ledger_date']['year'] . '-' . $record['ledger_date']['month']));
				elseif($period_type == "year")
					$date_str = $record['ledger_date']['year'];
				elseif($period_type == "quarter"){
					$quarter = ceil(date('m',strtotime($record['ledger_date']['year'] . "-" . $record['ledger_date']['month']))/3);
					$date_str = $record['ledger_date']['year'] . ' - Quarter #' . $quarter;
				}

				$dates[$date_str] = $date_str;
			}
			
			df_display(array("period_type"=>ucfirst($period_type), "period_select"=>$dates), 'close_ledger.html');		
		}

		
		//Pull all records in the period
		$date_range = ">=".date(2014);
		$records = df_get_records_array("general_ledger", array("ledger_date"=>$date_range));
			

		//Check for unposted batches in the period
		$unposted_records = array(); //Create empty array
		foreach($records as $record){ //Cycle through records to check for unposted ones, add to array
			if($record->val('post_status') != "Posted"){
				$unposted_records = array_merge($unposted_records,array($record->vals()));
			}
		}
		
		//If unposted batches, show them with error message
		if(count($unposted_records) > 0){
			df_display(array("period_type"=>ucfirst($period_type), "unposted"=>$unposted_records), 'close_ledger.html');
			return 0;
		}


		
			//If no unposted batches - 
				//Show / print trial balance, Income Statement & Balance Sheets
				//press submit button to confirm
				
					//Change Status to "Closed"
					//Create entry in table status
			
		df_display(array("period_type" => ucfirst($period)), 'close_ledger.html');		
		
	}
}


?>