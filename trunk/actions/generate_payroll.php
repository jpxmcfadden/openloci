<?php
class actions_generate_payroll {
	function handle(&$params){

		//Permission check
		if ( !isUser() )
			return Dataface_Error::permissionDenied("You are not logged in");	
	
		//Pull selected date if submitted, else null.
		$selected_date = isset($_GET['selected_date']) ? $_GET['selected_date'] : null;
		
		//Pull Payroll Configuration Information
		$payroll_config = df_get_record('_payroll_config', array('config_id'=>1));
		$payroll_config = $payroll_config->vals();


		//If data has not been submitted, or there was a problem with the submitted date - Display selection menu
		if($selected_date == null){
			//Auto Select - last week
				//$default_date = date('Y/m/d', strtotime('last '.$payroll_config['week_start'], time())); //This most recent (week start) day
				//$default_date = date('Y/m/d', strtotime('last '.$payroll_config['week_start'], strtotime($default_date))); //Previous (week start) day
			$default_date = date('Y/m/d', strtotime($payroll_config['week_start'] . " -2 week", time())); //Previous (week start) day
			
			//Display Selection Page
			df_display(array("default_date" => $default_date), 'generate_payroll.html');
		}
		else{
			//Get payroll period start date
			$period_start = $selected_date;
			
			//Calculate period end date
			if($payroll_config['payroll_period'] == "weekly")
				$period_end = date("Y/m/d", strtotime($period_start . " + 6 days"));
			else if($payroll_config['payroll_period'] == "2weeks")
				$period_end = date("Y/m/d", strtotime($period_start . " + 13 days"));
			else{
				$msg = "ERROR: Payroll period has not been set in the payroll configuration settings.";
				header('Location: index.php?-action=generate_payroll'.'&--msg='.urlencode($msg)); //Reset page w/ Error Msg
				return 0;
			}

			//Check to make sure date selected is on [week_start] day & that payroll for that period has not already been done.
			$period_record = df_get_record('payroll_period', array("period_start" => $selected_date));
			if($period_record != null){
				$msg = "ERROR: You have already generated payroll for the date: $selected_date";
				header('Location: index.php?-action=generate_payroll'.'&--msg='.urlencode($msg)); //Reset page w/ Error Msg.
				return 0;
			}
			else if(date("l", strtotime($selected_date)) != $payroll_config['week_start']){
				$msg = "ERROR: The selected date: $selected_date, is a " . date("l", strtotime($selected_date)) . ". The system is configured to start payroll on " . $payroll_config['week_start'] . "s. Please check and change the date accordingly.";
				header('Location: index.php?-action=generate_payroll'.'&--msg='.urlencode($msg)); //Reset page w/ Error Msg
				return 0;
			}

			//Check to insure there are no NULL values for "start_time" in the entire time_log table. If so, user must fix before running payroll.
			$employee_timelogs = df_get_records_array('time_logs', array('start_time'=>'=')); //Query any NULL values.
			if($employee_timelogs != null){
				$msg = "ERROR: The following entries have invalid *Arrive Times* in the Time Log and must be corrected before payroll can be processed: ";
				foreach($employee_timelogs as $timelog){
					$employee = df_get_record('employees', array('employee_id' => $timelog->val('employee_id')));
					$msg .= "(" . $employee->val('first_name') . " " . $employee->val('last_name') . " - Log ID " . $timelog->val('log_id') . " " . $timelog->val('start_time') . ")";
				}
				header('Location: index.php?-action=generate_payroll'.'&--msg='.urlencode($msg)); //Reset page w/ Error Msg
				return 0;
			}
						
			//Check to insure there are no NULL values for "end_time" in the selected payroll period. If so, user must fix before running payroll.
			$employee_timelogs = df_get_records_array('time_logs', array('start_time'=>"> $period_start AND < $period_end", 'end_time'=>'='));
			if($employee_timelogs != null){
				$msg = "ERROR: The following entries have invalid *Depart Times* in the Time Log for the selected period and must be corrected before payroll can be processed: ";
				foreach($employee_timelogs as $timelog){
					$employee = df_get_record('employees', array('employee_id' => $timelog->val('employee_id')));
					$msg .= "(" . $employee->val('first_name') . " " . $employee->val('last_name') . " - Log ID " . $timelog->val('log_id') . " " . $timelog->val('start_time') . ")";
				}
				header('Location: index.php?-action=generate_payroll'.'&--msg='.urlencode($msg)); //Reset page w/ Error Msg
				return 0;
			}



			
			//If there are no errors with the date selection or time logs, start collecting the payroll data
			
			$payroll_data = array();
			$payroll_data['period_start'] = $period_start;
			$payroll_data['period_end'] = $period_end;
			$payroll_data['month_period_number'] = monthPeriod($period_start, $payroll_config['payroll_period']);

			
			//Pull all active employees
			$employees = df_get_records_array('employees', array('active'=>'Y'));
			foreach($employees as $payroll_entry => $employee){
				//Get basic employee information
				$employee_id = $employee->val('employee_id');
				$payroll_data[$payroll_entry]['employee_id'] = $employee->val('employee_id');
				$payroll_data[$payroll_entry]['name'] = $employee->val('first_name') . ' ' . $employee->val('last_name');
				
				//Check if employee is salary or hourly, and assign hours accordingly
				if($employee->val('employee_type') == "Salary")
					$payroll_data[$payroll_entry]['hours'] = "Salary";
				else if($employee->val('employee_type') == "Hourly")
					$payroll_data[$payroll_entry]['hours'] = 0;
				else
					return false;
				
				//Employee hours
					$employee_timelogs = df_get_records_array('time_logs', array('employee_id'=>$employee_id, 'start_time'=>"> $period_start AND < $period_end"));

					foreach($employee_timelogs as $entry => $timelog){
						//Set status to 'locked', to prevent changes to the record after this point.
						$timelog->setValues(array('status'=>"Locked")); //Set data
						$check = $timelog->save(null, true); //Check Permissions & Save

						//Calculate hours from time logs.
						$start_time = strtotime(Dataface_converters_date::datetime_to_string($timelog->val('start_time')));
						$end_time = strtotime(Dataface_converters_date::datetime_to_string($timelog->val('end_time')));					
						$hours = number_format((($end_time - $start_time) / 3600),1);
						$payroll_data[$payroll_entry]['hours'] += $hours;
					}
				
				//Income
					$query = array(
						'employee_id'=>$employee_id,
						'start_date'=>"<= $period_start OR =",
						'end_date'=>">= $period_start OR =",
						'repeat_period'=>"All OR " . $payroll_data['month_period_number']
					);
					
					$item_entries = df_get_records_array('payroll_income', $query);
					foreach($item_entries as $entry => $item_entry){
						$entry_type = df_get_record('payroll_income_type', array("type"=>$item_entry->val('type')));
					
						$payroll_data[$payroll_entry]['income'][$entry]['employee'] = $item_entry->val('employee_id');
						$payroll_data[$payroll_entry]['income'][$entry]['description'] = $entry_type->val('name');
						$payroll_data[$payroll_entry]['income'][$entry]['amount_base'] = $item_entry->val('amount_base');
						$payroll_data[$payroll_entry]['income'][$entry]['amount_percent'] = $item_entry->val('amount_percent');
						$payroll_data[$payroll_entry]['income'][$entry]['account_number'] = $entry_type->val('account_number');
					}
				//Deductions
					$item_entries = df_get_records_array('payroll_deductions', $query);
					foreach($item_entries as $entry => $item_entry){
						$entry_type = df_get_record('payroll_deductions_type', array("type"=>$item_entry->val('type')));

						$payroll_data[$payroll_entry]['deductions'][$entry]['employee'] = $item_entry->val('employee_id');
						$payroll_data[$payroll_entry]['deductions'][$entry]['description'] = $entry_type->val('name');
						$payroll_data[$payroll_entry]['deductions'][$entry]['amount_base'] = $item_entry->val('amount_base');
						$payroll_data[$payroll_entry]['deductions'][$entry]['amount_percent'] = $item_entry->val('amount_percent');
						$payroll_data[$payroll_entry]['deductions'][$entry]['account_number'] = $entry_type->val('account_number');
					}
				//Contributions
					$item_entries = df_get_records_array('payroll_contributions', $query);
					foreach($item_entries as $entry => $item_entry){
						$entry_type = df_get_record('payroll_contributions_type', array("type"=>$item_entry->val('type')));

						$payroll_data[$payroll_entry]['contributions'][$entry]['employee'] = $item_entry->val('employee_id');
						$payroll_data[$payroll_entry]['contributions'][$entry]['description'] = $entry_type->val('name');
						$payroll_data[$payroll_entry]['contributions'][$entry]['amount_base'] = $item_entry->val('amount_base');
						$payroll_data[$payroll_entry]['contributions'][$entry]['amount_percent'] = $item_entry->val('amount_percent');
						$payroll_data[$payroll_entry]['contributions'][$entry]['account_number'] = $entry_type->val('account_number');
					}
			}

			//echo "<pre>";
			//print_r($payroll_data);
			//echo "</pre>";
			//echo "The payroll period from $period_start to $period_end has been created.<br>";

			//Create new payroll period record
			$payroll_period_record = new Dataface_Record('payroll_period', array()); //Create new record
			$payroll_period_record->setValues(array('period_start'=>$period_start,'period_end'=>$period_end)); //Set data
//			$check = $payroll_period_record->save(null, true); //Check Permissions & Save


			$payroll_entry = 0;
			while(isset($payroll_data[$payroll_entry])){
			
				//Create General Entry Record
				$payroll_entry_record = new Dataface_Record('payroll_entries', array()); //Create new entry record
				$payroll_entry_record->setValues(array(
													'payroll_period_id'=>$payroll_period_record->val('payroll_period_id'),
													'employee_id'=>$payroll_data[$payroll_entry]["employee_id"],
													));
//				$check = $payroll_entry_record->save(null, true); //Check Permissions & Save

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
																'description'=>$section_entry['description'],
																'amount_base'=>$section_entry['amount_base'],
																'amount_percent'=>$section_entry['amount_percent'],
																'account_number'=>$section_entry['account_number']
																));
//							$check = $payroll_entry_section_record->save(null, true); //Check Permissions & Save
						}
					}
				}
				
				$payroll_entry++;
				
			}
			
			$msg = "The payroll period from $period_start to $period_end has been created. Click here to proceed to ...";
			header('Location: index.php?-action=dashboard'.'&--msg='.urlencode($msg)); //Go to dashboard.

			df_display(array("default_date" => $period_start), 'generate_payroll.html');


		}
	}
}

//Function to take a date and starting day of the week, and return the Week Number
//$dte = date in string format (eg. "2014/02/06")
//$wsd = day of the week in string format (eg. "Sunday")
function weekNumber($dte, $wsd){
	//Day of the Month
	$d = date('j',strtotime($dte));

	//Day of the Week, where $wsd is the first day of the week.
	$w = date('w',strtotime($dte)) + 1 - date('w', strtotime($wsd));
	if($w < 1)
		$w+=7;

	//Day of the week the month starts on (0-6).
	$wk = ($w-($d%7))%7;
	if($wk < 0)
		$wk+=7;
		
	//Week #
	$W = ceil(($wk+($d)) / 7);
return $W;
}


//Function to take the start and end dates and payroll period, and return the Payroll Period Number for the month.
//The payroll period with the 1st of the month included in it is considered the first payroll period of the respective month, even if the 1st is the last day of the payroll period.
//$dte = start date in string format (eg. "2014/02/06")
//$pp = payroll period in string format (eg. "weekly")
function monthPeriod($dte, $pp){
	//Parse the payroll period into days.
	if($pp == "weekly")
		$days = 7;
	else if($pp == "2weeks")
		$days = 14;
	else if($pp == "bimonthly"){
		//If start date <= halfway (rounded up), period = 1, else period = 2
		if(date('j',strtotime($dte)) <= ceil(date('t', strtotime($dte)) / 2))
			return 1;
		else
			return 2;
	}
	else if($pp == "monthly")
		return 1; //Monthly payroll, being done only once a month will always be the first payroll period of the month. Note: This option isn't actually available.
	else //Payroll Period was not defined properly.
		return -1;

	//Day of the Month
	$d = date('j',strtotime($dte . "+ $days days"));
	
	//Assume 1st period of the month to start. Subtract $days from $d until $d < $days.
	$period = 1;
	while($d > $days){
		$period++;
		$d -= $days;
	}

	return $period;
}

?>