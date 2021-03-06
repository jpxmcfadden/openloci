<?php
class actions_generate_payroll {
	function handle(&$params){

		//Permission check
		if ( !isUser() )
			return Dataface_Error::permissionDenied("You are not logged in");
			
		$payroll_perms = get_userPerms('payroll');
		if(!($payroll_perms == "edit" || $payroll_perms == "post" || $payroll_perms == "close"))
			return Dataface_Error::permissionDenied("You do not have the proper permissions to Generate Payroll");
	
		//Pull selected date if submitted, else null.
		$selected_date = isset($_GET['selected_date']) ? $_GET['selected_date'] : null;
		
		//Check for holidays.
		$use_holidays = isset($_GET['use_holidays']) ? $_GET['use_holidays'] : null;

		//Pull Payroll Configuration Information
		$payroll_config = df_get_record('_payroll_config', array('config_id'=>1));
		$payroll_config = $payroll_config->vals();

		//If data has not been submitted, or there was a problem with the submitted date, or the user hit the cancel button - Display selection menu
		if($selected_date == null || $use_holidays == "Cancel"){
		

			//Auto Select - last week
				//$default_date = date('Y/m/d', strtotime('last '.$payroll_config['week_start'], time())); //This most recent (week start) day
				//$default_date = date('Y/m/d', strtotime('last '.$payroll_config['week_start'], strtotime($default_date))); //Previous (week start) day
			$default_date = date('Y/m/d', strtotime($payroll_config['week_start'] . " -2 week", time())); //Previous (week start) day
			
			//Display Selection Page
			df_display(array("default_date" => $default_date, "weekstart"=>$payroll_config['week_start']), 'generate_payroll.html');
		}
		else{
			//Get payroll period start date
			$period_start = $selected_date;
			
			//Calculate period variables - (end date, salary/ot hours, etc)
			if($payroll_config['payroll_period'] == "weekly"){
				$period_end = date("Y/m/d", strtotime($period_start . " + 6 days"));
				$period_hours = 40;
				$annual_periods = 52;
			}
			else if($payroll_config['payroll_period'] == "2weeks"){
				$period_end = date("Y/m/d", strtotime($period_start . " + 13 days"));
				$period_hours = 80;
				$annual_periods = 26;
			}
			else{
				$msg = "ERROR: Payroll period has not been set in the payroll configuration settings.";
				header('Location: index.php?-action=generate_payroll'.'&--msg='.urlencode($msg)); //Reset page w/ Error Msg
				return 0;
			}

			//Initialize the holiday hours variable.
			$holiday_hours = 0;

			
			//**********************<Check for Errors>**********************//
			$errors = 0;
			$err_msg = "";
			
			//Check to make sure date selected is on [week_start] day & that payroll for that period has not already been done.
			$period_record = df_get_record('payroll_period', array("period_start" => $selected_date));
			if($period_record != null){
				$err_msg .= "ERROR: You have already generated payroll for the date: $selected_date\n";
				$errors++;
			}
			else if(date("l", strtotime($selected_date)) != $payroll_config['week_start']){
				$err_msg .= "ERROR: The selected date: $selected_date, is a " . date("l", strtotime($selected_date)) . ". The system is configured to start payroll on " . $payroll_config['week_start'] . "s. Please check and change the date accordingly.\n";
				$errors++;
			}

			//Check to insure there are no NULL values for "start_time" in the entire time_log table. If so, user must fix before running payroll.
			$employee_timelogs = df_get_records_array('time_logs', array('start_time'=>'=')); //Query any NULL values.
			if($employee_timelogs != null){
				$err_msg = "ERROR: The following entries have invalid *Arrive Times* in the Time Log and must be corrected before payroll can be processed: ";
				foreach($employee_timelogs as $timelog){
					$employee = df_get_record('employees', array('employee_id' => $timelog->val('employee_id')));
					$err_msg .= "(" . $employee->val('first_name') . " " . $employee->val('last_name') . " - Log ID " . $timelog->val('log_id') . " " . $timelog->val('start_time') . ")\n";
				}
				$errors++;
			}
						
			//Check to insure there are no NULL values for "end_time" in the selected payroll period. If so, user must fix before running payroll.
			$employee_timelogs = df_get_records_array('time_logs', array('start_time'=>">= $period_start AND <= $period_end", 'end_time'=>'='));
			if($employee_timelogs != null){
				$err_msg = "ERROR: The following entries have invalid *Depart Times* in the Time Log for the selected period and must be corrected before payroll can be processed: ";
				foreach($employee_timelogs as $timelog){
					$employee = df_get_record('employees', array('employee_id' => $timelog->val('employee_id')));
					$err_msg .= "(" . $employee->val('first_name') . " " . $employee->val('last_name') . " - Log ID " . $timelog->val('log_id') . " " . $timelog->val('start_time') . ")\n";
				}
				$errors++;
			}

			//Check to insure that all 'active' employees have been appropriately set up.
			$employees = df_get_records_array('employees', array('active'=>'Y'));
			foreach($employees as $payroll_entry => $employee){
				//Check to make sure employee type (salary / hourly) is set. - This should only occur if payroll information has not been set up at all for an employee.
				if($employee->val("employee_type") != "Salary" && $employee->val("employee_type") != "Hourly"){
					$err_msg .= "The following employee's employment type status (Salary / Hourly) has not been set up correctly: " . $employee->val("last_name") . ", " . $employee->val("first_name") . "\n";
					$errors++;
				}
			
				//Load wage accounts for employee
				$wage_accounts = df_get_records_array("employees_wage_accounts", array("employee_id"=>$employee->val("employee_id")));

				//Check to insure that there is at least 1 wage expense account for all employees. - This should only occur if payroll information has not been set up at all for an employee.
				if(empty($wage_accounts)){
					$err_msg .= "The following employee's wage expense accounts have not been set up: " . $employee->val("last_name") . ", " . $employee->val("first_name") . "\n";
					$errors++;
				}
				//Check to insure that there is at least 1 overtime wage expense account for all hourly employees.
				elseif($employee->val("employee_type") == "Hourly"){
					$ot_wage_accts = 0;
					foreach($wage_accounts as $wage_account)
						if($wage_account->val("overtime") == 1)
							$ot_wage_accts++;
							
					if($ot_wage_accts == 0){
						$err_msg .= "The following hourly employee is missing an overtime wage expense account: " . $employee->val("last_name") . ", " . $employee->val("first_name") . "\n";
						$errors++;
					}
				}
			}			

			//If there are any errors, display them and return to the initial date selection screen.
			if($errors > 0){
				header('Location: index.php?-action=generate_payroll'.'&--msg='.urlencode($err_msg)); //Reset page w/ Error Msg
				return 0;
			}
			
			//**********************</Check for Errors>**********************//
			//If there are no errors with the date selection, time logs, or employee file, start collecting the payroll data
			
			$payroll_data = array();
			$payroll_data['period_start'] = $period_start;
			$payroll_data['period_end'] = $period_end;
			$payroll_data['month_period_number'] = monthPeriod($period_start, $payroll_config['payroll_period']);

			//**********************<Check for Holidays>**********************//
			//If we haven't checked for holidays yet, or we have and the answer was to use them
			if($use_holidays != "No"){ //Same as: ($use_holidays == null || $use_holidays == "Yes")
				//Load holiday records
				$holiday_records = df_get_records_array("_payroll_config_holiday",array("holiday_date"=>">= $period_start AND <= $period_end"));

				//If there are holiday records... 
				if($holiday_records != null){
					//If the user hasn't selected whether to use them or not, give them the select option.
					if($use_holidays == null){
						$holiday_hours_total = 0;
					
						//Create labels
						foreach($holiday_records as $key => $holiday_record){
							$holidays[$key]["description"] = $holiday_record->val("description");
							$holidays[$key]["date"] = $holiday_record->display("holiday_date") . " (" .date("l",strtotime($holiday_record->display("holiday_date"))). ")";
							$holidays[$key]["hours"] = $holiday_record->val("holiday_hours");
							$holiday_hours_total += $holiday_record->val("holiday_hours");
						}

						//Display the "use holidays y/n page", and stop page execution.
						df_display(array("selected_date" => $selected_date, "holidays"=>$holidays, "hours"=>$holiday_hours_total), 'generate_payroll_holiday_confirm.html');
						return 0;
					}

					//Otherwise, get the total # of holiday hours for the payroll period. - if($use_holidays == "Yes") is implied
					foreach($holiday_records as $key => $holiday_record){
						$holiday_hours += $holidays[$key]["hours"] = $holiday_record->val("holiday_hours");
					}
				}
			}
			
			//**********************</Check for Holidays>**********************//
			
			//Go through all active employees
			foreach($employees as $payroll_entry => $employee){
				//Get basic employee information
				$employee_id = $employee->val('employee_id');
				$payroll_data[$payroll_entry]['employee_id'] = $employee->val('employee_id');
				$payroll_data[$payroll_entry]['name'] = $employee->val('first_name') . ' ' . $employee->val('last_name');

				//Load wage accounts for employee
				$wage_accounts = df_get_records_array("employees_wage_accounts", array("employee_id"=>$employee->val("employee_id")));

				
				//Check to insure all necessary types are assigned
				//Federal Income Tax
				//*State Income Tax - From Employee -> only if an entry exists
				//FICA - Deduction & Contribution
				//Medicare - Deduction & Contribution
				//FUTA
				//*SUTA - From Employee


				//Holiday Hours
				$payroll_data[$payroll_entry]['holiday_hours'] = $holiday_hours;
				
				//Check if employee is salary or hourly, and assign hours accordingly
				if($employee->val('employee_type') == "Salary"){ //Salary
					//Calculate the number of work hours to be the number of hours in the payroll period.
					//	If there are holiday hours, subtract the holiday hours from regular salary hours and add as holiday pay. ($holiday_hours is normally 0)
					$regular_hours = $period_hours - $holiday_hours;
					$payroll_data[$payroll_entry]['regular_hours'] = $regular_hours;
				}
				else{ //Hourly
					$worked_hours = 0;
					$regular_hours = 0;
					$overtime_hours = 0;

					//Calculate hours from employee time logs
					$employee_timelogs = df_get_records_array('time_logs', array('employee_id'=>$employee_id, 'start_time'=>">= $period_start AND <= $period_end"));

					foreach($employee_timelogs as $entry => $timelog){
						//Set status to 'locked', to prevent changes to the record after this point.
						$timelog->setValues(array('status'=>"Locked")); //Set data
						$check = $timelog->save(null, true); //Check Permissions & Save

						//Calculate hours from time logs.
						$start_time = strtotime(Dataface_converters_date::datetime_to_string($timelog->val('start_time')));
						$end_time = strtotime(Dataface_converters_date::datetime_to_string($timelog->val('end_time')));					
						$hours = number_format((($end_time - $start_time) / 3600),1);
						
						//Check if time log entry has been assigned to a call slip. If so, use the CS type - for keeping track of expense accounts.
						if($timelog->val("category") == "CALL"){
							$call_slip_record = df_get_record("call_slips", array("call_id"=>$timelog->val("callslip_id")));
							$hour_type = strtolower($call_slip_record->val("type"));

							//CS Time Income
							if(!isset($payroll_data[$payroll_entry]['income']["hourly_" . $hour_type])){ //If null assign full entry
								//$payroll_data[$payroll_entry][$hour_type] = $hours;
								$income_type_record = df_get_record("payroll_income_type",array("type_id"=>$payroll_config["expense_" . $hour_type . "_type"]));
								
								$payroll_data[$payroll_entry]['income']["hourly_" . $hour_type]['employee'] = $employee_id;
								$payroll_data[$payroll_entry]['income']["hourly_" . $hour_type]['type'] = $payroll_config["expense_" . $hour_type . "_type"];
								$payroll_data[$payroll_entry]['income']["hourly_" . $hour_type]['taxable'] = 1;
								$payroll_data[$payroll_entry]['income']["hourly_" . $hour_type]['amount_base'] = "";
								$payroll_data[$payroll_entry]['income']["hourly_" . $hour_type]['amount_multiply'] = $employee->val('pay_rate');
								$payroll_data[$payroll_entry]['income']["hourly_" . $hour_type]['account_number'] = $income_type_record->val('account_number');

								//Assign Hours
								$payroll_data[$payroll_entry]['income']["hourly_" . $hour_type]['hours'] = $hours;
							}
							else{
								//Add Hours
								$payroll_data[$payroll_entry]['income']["hourly_" . $hour_type]['hours'] += $hours;
							}
						}
						else
							$regular_hours += $hours;

						//Add to total hours worked.
						$worked_hours += $hours;
					}

					//For Reference
					$payroll_data[$payroll_entry]['total_log_hours'] = $worked_hours;

					//Assign overtime if over 40 hours / week  --- *** NEED SOME STUFF HERE TO CALCULATE 40hrs/week IF PAY PERIOD != WEEKLY ***
					//Currently calculates based on period, not weeks - *** need to change this.
					//Include holiday hours in regular time
					if(($worked_hours + $holiday_hours) > $period_hours){
						//$payroll_data[$payroll_entry]['overtime_hours'] = ($worked_hours + $holiday_hours) - $period_hours;
						$overtime_hours = ($worked_hours + $holiday_hours) - $period_hours;
					}

				}

					//Regular Pay Income Entry - Check to make sure hours > 0, & loop through the non-overtime wage accounts and assign as appropriate to each.
					if($regular_hours > 0){
						foreach($wage_accounts as $entry => $wage_account){
							//Regular Salary Income Entry
							if($wage_account->val("overtime") != 1){ //Exclude overtime accounts
								$payroll_data[$payroll_entry]['income']["regular_hours_" . $entry]['employee'] = $employee_id;
								$payroll_data[$payroll_entry]['income']["regular_hours_" . $entry]['type'] = $payroll_config["expense_wage_type"];
								$payroll_data[$payroll_entry]['income']["regular_hours_" . $entry]['taxable'] = 1;
								$payroll_data[$payroll_entry]['income']["regular_hours_" . $entry]['amount_base'] = "";
								$payroll_data[$payroll_entry]['income']["regular_hours_" . $entry]['amount_multiply'] = $employee->val('pay_rate');
								$payroll_data[$payroll_entry]['income']["regular_hours_" . $entry]['account_number'] = $wage_account->val('account_id');

								//Calculate the number of hours per account based on the percent assigned to it.
								$payroll_data[$payroll_entry]['income']["regular_hours_" . $entry]['hours'] = $regular_hours * ($wage_account->val('amount_percent') / 100.0);
							}
						}
					}
						
					//Overtime Pay Income Entry - Check to make sure hours > 0, & loop through the overtime wage accounts and assign as appropriate to each.
					if(isset($overtime_hours) && $overtime_hours > 0){
						foreach($wage_accounts as $entry => $wage_account){
							//Overtime Hourly Income Entry
							if($wage_account->val("overtime") == 1){ //Overtime accounts
								$payroll_data[$payroll_entry]['income']["overtime_hours_" . $entry]['employee'] = $employee_id;
								$payroll_data[$payroll_entry]['income']["overtime_hours_" . $entry]['type'] = $payroll_config["expense_overtime_type"];
								$payroll_data[$payroll_entry]['income']["overtime_hours_" . $entry]['taxable'] = 1;
								$payroll_data[$payroll_entry]['income']["overtime_hours_" . $entry]['amount_base'] = "";
								$payroll_data[$payroll_entry]['income']["overtime_hours_" . $entry]['amount_multiply'] = $employee->val('pay_rate')*0.5;
								$payroll_data[$payroll_entry]['income']["overtime_hours_" . $entry]['account_number'] = $wage_account->val('account_id');

								//Calculate the number of hours per account based on the percent assigned to it.
								$payroll_data[$payroll_entry]['income']["overtime_hours_" . $entry]['hours'] = $overtime_hours * ($wage_account->val('amount_percent') / 100.0);
							}
						}					
					}				
				
				
				
				
				
				
				
				
				
				//Create Payroll Entries Array
					$query = array(
						'employee_id'=>$employee_id,
						'start_date'=>"<= $period_start OR =",
						'end_date'=>">= $period_start OR =",
						'repeat_period'=>"All OR " . $payroll_data['month_period_number']
					);

				//Additional Income Entries
				
					//Holiday Hours Income Entry
					if($holiday_hours > 0){
						$holiday_type = df_get_record("payroll_income_type",array("type_id"=>$payroll_config["holiday_hours_type"]));
						$payroll_data[$payroll_entry]['income']["holiday"]['employee'] = $employee_id;
						$payroll_data[$payroll_entry]['income']["holiday"]['type'] = $payroll_config["holiday_hours_type"];
						$payroll_data[$payroll_entry]['income']["holiday"]['taxable'] = 1;
						$payroll_data[$payroll_entry]['income']["holiday"]['amount_base'] = "";
						$payroll_data[$payroll_entry]['income']["holiday"]['amount_multiply'] = $employee->val('pay_rate');
						$payroll_data[$payroll_entry]['income']["holiday"]['account_number'] = $holiday_type->val('account_number');
						$payroll_data[$payroll_entry]['income']["holiday"]['hours'] = $holiday_hours;
					}
						
					//Additional Income
					$item_entries = df_get_records_array('payroll_income', $query);
					foreach($item_entries as $entry => $item_entry){
						$entry_type = df_get_record('payroll_income_type', array("type_id"=>$item_entry->val('type')));
						$payroll_data[$payroll_entry]['income'][$entry]['employee'] = $item_entry->val('employee_id');
						$payroll_data[$payroll_entry]['income'][$entry]['type'] = $item_entry->val('type');
						$payroll_data[$payroll_entry]['income'][$entry]['taxable'] = $entry_type->val('taxable');
						$payroll_data[$payroll_entry]['income'][$entry]['hours'] = '';
						$payroll_data[$payroll_entry]['income'][$entry]['amount_base'] = $item_entry->val('amount_base');
						$payroll_data[$payroll_entry]['income'][$entry]['amount_multiply'] = $item_entry->val('amount_multiply');
						$payroll_data[$payroll_entry]['income'][$entry]['account_number'] = $entry_type->val('account_number');
					}

				//Deduction Entries
				
					//FICA Deduction Entry
					$entry = "fica";
						$entry_type = df_get_record('payroll_deductions_type', array("type_id"=>$payroll_config["fica_deduction_type"]));
						$payroll_data[$payroll_entry]['deductions'][$entry]['employee'] = $employee_id;
						$payroll_data[$payroll_entry]['deductions'][$entry]['type'] = $payroll_config["fica_deduction_type"];
						$payroll_data[$payroll_entry]['deductions'][$entry]['pre_tax'] = '';
						$payroll_data[$payroll_entry]['deductions'][$entry]['amount_base'] = '';
						$payroll_data[$payroll_entry]['deductions'][$entry]['amount_multiply'] = $payroll_config["fica_percent"];
						$payroll_data[$payroll_entry]['deductions'][$entry]['account_number'] = $entry_type->val('account_number');
						$payroll_data[$payroll_entry]['deductions'][$entry]['annual_limit'] = $entry_type->val('annual_limit');
						
					//Medicare Deduction Entry
					$entry = "medicare";
						$entry_type = df_get_record('payroll_deductions_type', array("type_id"=>$payroll_config["medicare_deduction_type"]));
						$payroll_data[$payroll_entry]['deductions'][$entry]['employee'] = $employee_id;
						$payroll_data[$payroll_entry]['deductions'][$entry]['type'] = $payroll_config["medicare_deduction_type"];
						$payroll_data[$payroll_entry]['deductions'][$entry]['pre_tax'] = '';
						$payroll_data[$payroll_entry]['deductions'][$entry]['amount_base'] = '';
						$payroll_data[$payroll_entry]['deductions'][$entry]['amount_multiply'] = $payroll_config["medicare_percent"];
						$payroll_data[$payroll_entry]['deductions'][$entry]['account_number'] = $entry_type->val('account_number');
						$payroll_data[$payroll_entry]['deductions'][$entry]['annual_limit'] = $entry_type->val('annual_limit');
						
					//Medicare Extra
					
					
					//Federal Income Tax
					$entry = "federal";
						$entry_type = df_get_record('payroll_deductions_type', array("type_id"=>$payroll_config["federal_type"]));
						$payroll_data[$payroll_entry]['deductions'][$entry]['employee'] = $employee_id;
						$payroll_data[$payroll_entry]['deductions'][$entry]['type'] = $payroll_config["federal_type"];
						$payroll_data[$payroll_entry]['deductions'][$entry]['pre_tax'] = '';
						$payroll_data[$payroll_entry]['deductions'][$entry]['amount_base'] = '';
						$payroll_data[$payroll_entry]['deductions'][$entry]['amount_multiply'] = '';
						$payroll_data[$payroll_entry]['deductions'][$entry]['account_number'] = $entry_type->val('account_number');
						$payroll_data[$payroll_entry]['deductions'][$entry]['annual_limit'] = $entry_type->val('annual_limit');
					
					//State Income Tax
					$entry = "state";
						$entry_type = df_get_record('payroll_deductions_type', array("type_id"=>$payroll_config["state_type"]));
						$payroll_data[$payroll_entry]['deductions'][$entry]['employee'] = $employee_id;
						$payroll_data[$payroll_entry]['deductions'][$entry]['type'] = $payroll_config["state_type"];
						$payroll_data[$payroll_entry]['deductions'][$entry]['pre_tax'] = '';
						$payroll_data[$payroll_entry]['deductions'][$entry]['amount_base'] = '';
						$payroll_data[$payroll_entry]['deductions'][$entry]['amount_multiply'] = '';
						$payroll_data[$payroll_entry]['deductions'][$entry]['account_number'] = $entry_type->val('account_number');
						$payroll_data[$payroll_entry]['deductions'][$entry]['annual_limit'] = $entry_type->val('annual_limit');

					//ADD CITY Income Tax HERE
						
					//Deductions
					$item_entries = df_get_records_array('payroll_deductions', $query);
					foreach($item_entries as $entry => $item_entry){
						$entry_type = df_get_record('payroll_deductions_type', array("type_id"=>$item_entry->val('type')));
						$payroll_data[$payroll_entry]['deductions'][$entry]['employee'] = $item_entry->val('employee_id');
						$payroll_data[$payroll_entry]['deductions'][$entry]['type'] = $item_entry->val('type');
						$payroll_data[$payroll_entry]['deductions'][$entry]['pre_tax'] = $entry_type->val('pre_tax');
						$payroll_data[$payroll_entry]['deductions'][$entry]['amount_base'] = $item_entry->val('amount_base');
						$payroll_data[$payroll_entry]['deductions'][$entry]['amount_multiply'] = $item_entry->val('amount_multiply');
						$payroll_data[$payroll_entry]['deductions'][$entry]['account_number'] = $entry_type->val('account_number');
						$payroll_data[$payroll_entry]['deductions'][$entry]['annual_limit'] = $entry_type->val('annual_limit');
					}
					
				//Contribution Entries  -- Auto Add: FICA, Medicare, FUTA, SUTA
					//FICA Contribution Entry
					$entry = "fica";
						$entry_type = df_get_record('payroll_contributions_type', array("type_id"=>$payroll_config["fica_contribution_type"]));
						$payroll_data[$payroll_entry]['contributions'][$entry]['employee'] = $employee_id;
						$payroll_data[$payroll_entry]['contributions'][$entry]['type'] = $payroll_config["fica_contribution_type"];
						$payroll_data[$payroll_entry]['contributions'][$entry]['amount_base'] = '';
						$payroll_data[$payroll_entry]['contributions'][$entry]['amount_multiply'] = $payroll_config["fica_percent"];
						$payroll_data[$payroll_entry]['contributions'][$entry]['account_number_liability'] = $entry_type->val('account_number_liability');
						$payroll_data[$payroll_entry]['contributions'][$entry]['account_number_expense'] = $entry_type->val('account_number_expense');
						$payroll_data[$payroll_entry]['contributions'][$entry]['annual_limit'] = $entry_type->val('annual_limit');

					//Medicare Contribution Entry
					$entry = "medicare";
						$entry_type = df_get_record('payroll_contributions_type', array("type_id"=>$payroll_config["medicare_contribution_type"]));
						$payroll_data[$payroll_entry]['contributions'][$entry]['employee'] = $employee_id;
						$payroll_data[$payroll_entry]['contributions'][$entry]['type'] = $payroll_config["medicare_contribution_type"];
						$payroll_data[$payroll_entry]['contributions'][$entry]['amount_base'] = '';
						$payroll_data[$payroll_entry]['contributions'][$entry]['amount_multiply'] = $payroll_config["medicare_percent"];
						$payroll_data[$payroll_entry]['contributions'][$entry]['account_number_liability'] = $entry_type->val('account_number_liability');
						$payroll_data[$payroll_entry]['contributions'][$entry]['account_number_expense'] = $entry_type->val('account_number_expense');
						$payroll_data[$payroll_entry]['contributions'][$entry]['annual_limit'] = $entry_type->val('annual_limit');

					//FUTA Contribution Entry
					$entry = "futa";
						$uta_record = df_get_record('_payroll_config_uta', array("state"=>"FED"));
						$entry_type = df_get_record('payroll_contributions_type', array("type_id"=>$uta_record->val('type_id')));
						$payroll_data[$payroll_entry]['contributions'][$entry]['employee'] = $employee_id;
						$payroll_data[$payroll_entry]['contributions'][$entry]['type'] = $uta_record->val("type_id");
						$payroll_data[$payroll_entry]['contributions'][$entry]['amount_base'] = '';
						$payroll_data[$payroll_entry]['contributions'][$entry]['amount_multiply'] =  $uta_record->val("percent");
						$payroll_data[$payroll_entry]['contributions'][$entry]['account_number_liability'] = $entry_type->val('account_number_liability');
						$payroll_data[$payroll_entry]['contributions'][$entry]['account_number_expense'] = $entry_type->val('account_number_expense');
						$payroll_data[$payroll_entry]['contributions'][$entry]['annual_limit'] = $entry_type->val('annual_limit');
					
					//SUTA Contribution Entry
					$entry = "suta";
						$uta_record = df_get_record('_payroll_config_uta', array("state"=>$employee->val("state")));
						$entry_type = df_get_record('payroll_contributions_type', array("type_id"=>$uta_record->val('type_id')));
						$payroll_data[$payroll_entry]['contributions'][$entry]['employee'] = $employee_id;
						$payroll_data[$payroll_entry]['contributions'][$entry]['type'] = $uta_record->val("type_id");
						$payroll_data[$payroll_entry]['contributions'][$entry]['amount_base'] = '';
						$payroll_data[$payroll_entry]['contributions'][$entry]['amount_multiply'] =  $uta_record->val("percent");
						$payroll_data[$payroll_entry]['contributions'][$entry]['account_number_liability'] = $entry_type->val('account_number_liability');
						$payroll_data[$payroll_entry]['contributions'][$entry]['account_number_expense'] = $entry_type->val('account_number_expense');
						$payroll_data[$payroll_entry]['contributions'][$entry]['annual_limit'] = $entry_type->val('annual_limit');
						
					//Contributions
					$item_entries = df_get_records_array('payroll_contributions', $query);
					foreach($item_entries as $entry => $item_entry){
						$entry_type = df_get_record('payroll_contributions_type', array("type"=>$item_entry->val('type')));

						$payroll_data[$payroll_entry]['contributions'][$entry]['employee'] = $item_entry->val('employee_id');
						$payroll_data[$payroll_entry]['contributions'][$entry]['type'] = $item_entry->val('type');
						$payroll_data[$payroll_entry]['contributions'][$entry]['amount_base'] = $item_entry->val('amount_base');
						$payroll_data[$payroll_entry]['contributions'][$entry]['amount_multiply'] = $item_entry->val('amount_multiply');
						$payroll_data[$payroll_entry]['contributions'][$entry]['account_number_liability'] = $entry_type->val('account_number_liability');
						$payroll_data[$payroll_entry]['contributions'][$entry]['account_number_expense'] = $entry_type->val('account_number_expense');
						$payroll_data[$payroll_entry]['contributions'][$entry]['annual_limit'] = $entry_type->val('annual_limit');
					}
			}

			//Create new payroll period record
			$payroll_period_record = new Dataface_Record('payroll_period', array()); //Create new record
			$payroll_period_record->setValues(array('period_start'=>$period_start,'period_end'=>$period_end)); //Set data
			$check = $payroll_period_record->save(null, true); //Check Permissions & Save


			$payroll_entry = 0;
			while(isset($payroll_data[$payroll_entry])){
			
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
																'amount_multiply'=>$section_entry['amount_multiply']
																));
							if($section == "income"){
								$payroll_entry_section_record->setValues(array(
																'taxable'=>$section_entry['taxable'],
																'hours'=>$section_entry['hours'],
																'account_number'=>$section_entry['account_number']
																));
							}
							else if($section == "deductions"){
								$payroll_entry_section_record->setValues(array(
																'pre_tax'=>$section_entry['pre_tax'],
																'annual_limit'=>$section_entry['annual_limit'],
																'account_number'=>$section_entry['account_number']
																));
							}
							else if($section ==  "contributions"){
								$payroll_entry_section_record->setValues(array(
																'annual_limit'=>$section_entry['annual_limit'],
																'account_number_liability'=>$section_entry['account_number_liability'],
																'account_number_expense'=>$section_entry['account_number_expense']
																));
							}
							
							$check = $payroll_entry_section_record->save(null, true); //Check Permissions & Save
						}
					}
				}
				
				$payroll_entry++;
				
			}
			
			//Go back to Dashboard w/ success message
			$msg = "The payroll period from $period_start to $period_end has been created. Please review and confirm to post.";
			header('Location: index.php?-action=browse&-table=payroll_period'.'&--msg='.urlencode($msg)); //Go to dashboard.

			//df_display(array("default_date" => $period_start), 'generate_payroll.html');

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