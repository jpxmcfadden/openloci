<?php
class actions_close_payroll {
	function handle(&$params){

		//Permission check
		if ( !isUser() )
			return Dataface_Error::permissionDenied("You are not logged in");
			
		if(get_userPerms('payroll') != "Close")
			return Dataface_Error::permissionDenied("You do not have the proper permissions to Close Payroll");

		//Pull Payroll Configuration Information
		$payroll_config = df_get_record('_payroll_config', array('config_id'=>1));

		//Get the payroll period, if the user has selected it - otherwise null
		$payroll_month = isset($_GET['payroll_month']) ? $_GET['payroll_month'] : null;

		//Check for close confirmation. If null select / confirm month, otherwise close month
		if(!isset($_GET['confirm'])){
		
			//-----------------------------------------------------------------------------------------------
			//Month Selection--------------------------------------------------------------------------------
			//-----------------------------------------------------------------------------------------------

			//If $payroll_month is null (user has not yet selected it)
			if($payroll_month == null){
			
				//Pull payroll period records with status = "Posted"
				$payroll_periods = df_get_records_array("payroll_period", array("status"=>"Posted"));
				
				//Create the list of all unposted payroll periods
				foreach($payroll_periods as $key => $period){
					$month = date("Y - F",strtotime($period->strval($payroll_config->val('close_date_option'))));

					//Get a list of months with "posted", but not "closed" payroll periods
					$payroll_months[$month] = $month;
				}
				
				//Display selection page
				if(isset($payroll_months))
					df_display(array("payroll_months" => $payroll_months), 'close_payroll.html');

				//If there are no payroll periods with status = "Posted"
				df_display(array(), 'close_payroll.html');
					
				//Should never get here... but just in case.
				return;
			}
			
			//-----------------------------------------------------------------------------------------------
			//Display Month Periods--------------------------------------------------------------------------
			//-----------------------------------------------------------------------------------------------

			//Pull payroll period records with the selected month
			$month = date("Y-m-d",strtotime($payroll_month)); //The Selected Month
			$next = date("Y-m-d",strtotime($payroll_month . "+ 1 month")); //The Month after the Selected Month (to get between dates)
			$payroll_periods = df_get_records_array("payroll_period", array($payroll_config->val('close_date_option')=>">= $month AND < $next"));
			
			foreach($payroll_periods as $key => $period){
				//$periods[$key] = $period->strval($payroll_config->val('close_date_option'));
				$periods[$key] = $period->strvals();
				
				//Check to make sure all payroll periods are posted before closing
				// * Added "Closed" just in case something goes wrong and only part of the month gets closed at some point (i.e. an error), this won't totally screw things up.
				if($period->val("status") != "Posted" && $period->val("status") != "Closed"){
					$msg = "One or more of the Payroll Periods in the selected month has not been posted. Please post, or select a different month.";
					header('Location: index.php?-action=close_payroll'.'&--msg='.urlencode($msg)); //Go to dashboard.
				}
			}
			
			df_display(array("periods" => $periods, "month" => date("Y-F", strtotime($month))), 'close_payroll.html');		
		
		}
		elseif($_GET['confirm'] == "confirm" && isset($payroll_month)){

			//-----------------------------------------------------------------------------------------------
			//Close Month--------------------------------------------------------------------------
			//-----------------------------------------------------------------------------------------------

			//Pull payroll period records with the selected month
			$month = date("Y-m-d",strtotime($payroll_month)); //The Selected Month
			$next = date("Y-m-d",strtotime($payroll_month . "+ 1 month")); //The Month after the Selected Month (to get between dates)
			$payroll_periods = df_get_records_array("payroll_period", array($payroll_config->val('close_date_option')=>">= $month AND < $next"));
			foreach($payroll_periods as $key => $period){
				$period->setValue('status', "Closed");
				$res = $period->save(null, true); //Save w/ permission check
				//$res = $period->save(); //Save w/ permission check

				//Check for errors.
				if ( PEAR::isError($res) ){
					// An error occurred
					///throw new Exception($res->getMessage());
					$msg = '<input type="hidden" name="--error" value="Unable to Close Payroll. This is most likely because you do not have the required permissions.">';
					header('Location: index.php?-action=close_payroll'.'&--msg='.urlencode($msg)); //Go to dashboard.
				}
				
				$period_entries = df_get_records_array("payroll_entries", array("payroll_period_id"=>$period->val("payroll_period_id")));
				foreach($period_entries as $entry){
					$entry->setValue('status', "Closed");
					$res = $entry->save(); //Save w/o permission check
				}
				
			}
			
			//Go back to Dashboard w/ success message
			$msg = "The month of " . date("F (Y)",strtotime($month)) . " has been closed.";
			header('Location: index.php?-action=dashboard'.'&--msg='.urlencode($msg)); //Go to dashboard.
		}
		else{
			//Go back to Dashboard w/ error message
			$msg = "An error occurred.";
			header('Location: index.php?-action=close_payroll'.'&--msg='.urlencode($msg)); //Go to dashboard.
		}
		
		
		//Pull Payroll Configuration Information
		//$payroll_config = df_get_record('_payroll_config', array('config_id'=>1));
		//$payroll_config = $payroll_config->vals();

		//Pull payroll entries from the selected payroll period
		//$payroll_entries = df_get_records_array("payroll_entries", array("payroll_period_id"=>$payroll_period));
		/*
		foreach($payroll_entries as $entry){
		
			echo $entry->val("payroll_entry_id") . "<br>";
			
			//Pull sections (income, deductions, & contributions) for each entry
			$sections = array('income', 'deductions', 'contributions');
			foreach($sections as $section){
				//Pull all records in the associated entry section
				$section_records = df_get_records_array('payroll_entries_'.$section, array("payroll_entry_id"=>$entry->val("payroll_entry_id")));
				foreach($section_records as $section_record){
					echo $section . " - " . $section_record->val("type") . "<br>";
				}
			}
			
			echo "<br><br>";
		}
		*/
		
		
		
		//Create new payroll period record
		//$payroll_period_record = new Dataface_Record('payroll_period', array()); //Create new record
		//$payroll_period_record->setValues(array('period_start'=>$period_start,'period_end'=>$period_end)); //Set data
		//$check = $payroll_period_record->save(null, true); //Check Permissions & Save

		/*
		//Create General Entry Record
		$payroll_entry_record = new Dataface_Record('payroll_entries', array()); //Create new entry record
		$payroll_entry_record->setValues(array(
											'payroll_period_id'=>$payroll_period_record->val('payroll_period_id'),
											'employee_id'=>$payroll_data[$payroll_entry]["employee_id"],
											'period_number'=>$payroll_data["month_period_number"]
											));
		$check = $payroll_entry_record->save(null, true); //Check Permissions & Save

		//Create Contribution / Deduction / Income Entry Records
		$sections = array('contributions', 'deductions', 'income');
		foreach($sections as $section){			
			if(isset($payroll_data[$payroll_entry][$section])){
				foreach($payroll_data[$payroll_entry][$section] as $section_entry){
					$payroll_entry_section_record = new Dataface_Record('payroll_entries_'.$section, array()); //Create new contributions entry record
					$payroll_entry_section_record->setValues(array(
														'payroll_period_id'=>$payroll_period_record->val('payroll_period_id'),
														'payroll_entry_id'=>$payroll_entry_record->val('payroll_entry_id'),
														'employee_id'=>$payroll_data[$payroll_entry]["employee_id"],
														'type'=>$section_entry['type'],
														'amount_base'=>$section_entry['amount_base'],
														'amount_multiply'=>$section_entry['amount_multiply'],
														'account_number'=>$section_entry['account_number']
														));
					if($section == "income"){
						$payroll_entry_section_record->setValues(array('taxable'=>$section_entry['taxable']));
						$payroll_entry_section_record->setValues(array('hours'=>$section_entry['hours']));
					}
					else if($section == "deductions")
						$payroll_entry_section_record->setValues(array('post_tax'=>$section_entry['post_tax']));
						$check = $payroll_entry_section_record->save(null, true); //Check Permissions & Save
				}
			}
		}
		*/
		



	
	}
}

?>