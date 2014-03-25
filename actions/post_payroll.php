<?php
class actions_post_payroll {
	function handle(&$params){

		//Permission check
		if ( !isUser() )
			return Dataface_Error::permissionDenied("You are not logged in");	

		//Get the payroll period, if the user has selected it - otherwise null
		$payroll_period = isset($_GET['payroll_period']) ? $_GET['payroll_period'] : null;

		
		/*
		
		GUI: Form: Select Field - any unposted payroll periods
		
		*/

		//If $payroll_period is null (user has not yet selected it) - Pull all unposted payroll periods and display the selection page.
		if($payroll_period == null){
			//Pull records with status = "Reviewed" (null)
			$payroll_periods = df_get_records_array("payroll_period", array("status"=>"Reviewed"));
			
			//Create the list of all unposted payroll periods
			foreach($payroll_periods as $key => $period){
				//List by record id - for passing on 
				$period_list[$key] = $period->val('payroll_period_id');
				
				//List by record dates - for visual options in the list
				$period_visual[$key] = $period->strval('period_start') . " to " . $period->strval('period_end');
			}
			//Display selection page
			df_display(array("payroll_periods" => $period_list, "payroll_periods_display" => $period_visual), 'post_payroll.html');

			//Should never get here... but just in caase.
			return;
		}
		
		//-----------------------------------------------------------------------------------------------
		//-----------------------------------------------------------------------------------------------
		//-----------------------------------------------------------------------------------------------
		
		/*
		
		Pull all entries from selected payroll period
		Create GL Journal Entry
			- All information in entries (income / deductions / contributions)
			- Employeer information
			Payroll Entry: Debit (Income Entries) <---> Credit (Deduction Entries / Net Payroll Payable)
			Payroll Entry: Debit (Contributions / Expenses [eg FICA expense]) <---> Credit (Payables [eg FICA payables]) ---Confused about this one***

		Set Payroll Period to "Posted"
		Set all Entries to "Posted"
		
		*/
		
		//Pull Payroll Configuration Information
		//$payroll_config = df_get_record('_payroll_config', array('config_id'=>1));
		//$payroll_config = $payroll_config->vals();

		//Pull payroll entries from the selected payroll period
		$payroll_entries = df_get_records_array("payroll_entries", array("payroll_period_id"=>$payroll_period));
		
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
		
		//Go back to Dashboard w/ success message
		//$msg = "The payroll period from $period_start to $period_end has been created. Click here to proceed to ...";
		//header('Location: index.php?-action=dashboard'.'&--msg='.urlencode($msg)); //Go to dashboard.

		df_display(array(), 'post_payroll.html');


	
	}
}

?>