<?php

//Use global scope variables for totals so that other sections can access them.
$totals_gross_income = 0;
$totals_net_income = 0;
$totals_deductions = 0;
		
class tables_payroll_period {

	function getTitle(&$record){
		return "Payroll Period: " . $record->strval('period_start').' to '.$record->strval('period_end');
	}

	//Permissions
	function getPermissions(&$record){
		//Check if the user is logged in & what their permissions for this table are.
		if( isUser() ){
			$userperms = get_userPerms('payroll');
			if($userperms == "view")
				return Dataface_PermissionsTool::getRolePermissions("READ ONLY"); //Assign Read Only Permissions
			elseif($userperms == "edit" || $userperms == "post" || $userperms == "close"){
				//Check status, determine if record should be uneditable.
				if ( isset($record) && $record->val('status') == "Closed" && !isset($_GET['confirm']) ) //No edit after Closed - unless the confirm button was *just* pressed (Since the "post" button is within the record view & the action to save happens on page reload, must make this exception, otherwise permissions will keep it from saving as "posted")
						return Dataface_PermissionsTool::getRolePermissions('NO_EDIT_DELETE');
				return Dataface_PermissionsTool::getRolePermissions(myRole()); //Assign Permissions based on user Role (typically USER)
			}
		}

		//Default: No Access
		return Dataface_PermissionsTool::NO_ACCESS();
	}

	function section__entries(&$record){
		$app =& Dataface_Application::getInstance(); 
		$query =& $app->getQuery();
		$childString = '';
		
		//Check if the payroll period has been posted, or is still open.
		if($record->val("status") == "Posted")
			$payroll_data = $this->payroll_from_entries($record);
		else
			$payroll_data = $this->calculate_payroll($record);


		//Traverse through all data in the payroll_data array
		foreach($payroll_data as $payroll_entry){
			//Check to see if the entry is an array (if not ignore)
			if(is_array($payroll_entry)){

				//Pull the Employee Record for the associated Employee
				$childString .= "<b><u>" . $payroll_entry["employee_name"] . "</a></u></b>";
				
				//If the Payroll Period has not yet been closed, include a link to edit the entry.
				if($record->val("status") != "Closed")
					$childString .= " - <a href=index.php?-table=payroll_entries&-action=edit&-recordid=".$payroll_entry["entry_id_url"]."&-review=" . $record->getID() . ">Edit this Entry</a><br><br>";

				//Indent the following sections
				$childString .= '<table><tr><td style="padding-left:15px; padding-right:15px; width: 600px;">';

					//********** INCOME ENTRIES **********//
					//Create Table
					$childString .= '<b><u>Income</u></b><br><br>';
					$childString .= '<table class="view_add"><tr>
										<th>Description</th>
										<th>Account</th>
										<th>Taxable</th>
										<th>Hours</th>
										<th>Amount Base</th>
										<th>Multiply</th>
										<th>Total</th>'
										. ($record->val("status") == "Posted" ? '<th>YTD</th>' : '') . //If Posted, include YTD
										'</tr>';

					foreach($payroll_entry["income"] as $addition_entry){
						$childString .= '<tr>
											<td>'.$addition_entry["description"].'</td>
											<td>'.$addition_entry["account"].'</td>
											<td>'.$addition_entry["taxable"].'</td>
											<td>'.$addition_entry["hours"].'</td>
											<td>'.$addition_entry["base"].'</td>
											<td>'.$addition_entry["multiply"].'</td>
											<td align="right">$'.$addition_entry["total_amount"].'</td>'
											. ($record->val("status") == "Posted" ? '<td>'.$addition_entry["ytd"].'</td>' : '') . //If Posted, include YTD
										'</tr>';
					}
								
					$childString .= '</table><br><br>';

					//********** DEDUCTION ENTRIES **********//
					//Create Table
					$childString .= '<b><u>Deductions</u></b><br><br>';
					$childString .= '<table class="view_add"><tr>
										<th>Description</th>
										<th>Account</th>
										<th>Pre Tax</th>
										<th>Amount Base</th>
										<th>Multiply</th>
										<th>Annual Limit</th>
										<th>Total</th>
										<th>YTD</th>
									</tr>';

					foreach($payroll_entry["deductions"] as $addition_entry){
						$childString .= '<tr>
											<td>'.$addition_entry["description"].'</td>
											<td>'.$addition_entry["account"].'</td>
											<td>'.$addition_entry["pre_tax"].'</td>
											<td>'.$addition_entry["base"].'</td>
											<td>'.$addition_entry["multiply"].'</td>
											<td>'.$addition_entry["annual_limit"].'</td>
											<td align="right">$'.$addition_entry["total_amount"].'</td>
											<td align="right">$'.$addition_entry["ytd"].'</td>
										</tr>';
					}
								
					$childString .= '</table><br><br>';
					
					//********** CONTRIBUTION ENTRIES **********//
					//Create Table
					$childString .= '<b><u>Contributions</u></b><br><br>';
					$childString .= '<table class="view_add"><tr>
										<th>Description</th>
										<th>Liability Account</th>
										<th>Expense Account</th>
										<th>Amount Base</th>
										<th>Multiply</th>
										<th>Annual Limit</th>
										<th>Total</th>
										<th>YTD</th>
										</tr>';

					foreach($payroll_entry["contributions"] as $addition_entry){
						$childString .= '<tr>
											<td>'.$addition_entry["description"].'</td>
											<td>'.$addition_entry["account_liability"].'</td>
											<td>'.$addition_entry["account_expense"].'</td>
											<td>'.$addition_entry["base"].'</td>
											<td>'.$addition_entry["multiply"].'</td>
											<td>'.$addition_entry["annual_limit"].'</td>
											<td align="right">$'.$addition_entry["total_amount"].'</td>
											<td align="right">$'.$addition_entry["ytd"].'</td>
										</tr>';
					}
								
					$childString .= '</table>';		

					//********** TOTALS **********//
					$childString .= '</td><td style="padding-left:15px; border-left:1px solid #000000; vertical-align:top;">';
					$childString .= '<b><u>Totals</u></b><br><br>';
					$childString .= '<table class="view_add_totals">';

					$childString .= '<tr><th>Gross Income</th><td>$' . $payroll_entry["gross_income"] .
										'</td><th>YTD</th><td>$'. $payroll_entry["gross_income_ytd"] . '</td></tr>';
					$childString .= '<tr><th>Wages</th><td>$' . $payroll_entry["wages"] .
										'</td><th>YTD</th><td>$'. $payroll_entry["wages_ytd"] . '</td></tr>';
					$childString .= '<tr><th>SS Wages</th><td>$' . $payroll_entry["ss_wages"] .
										'</td><th>YTD</th><td>$'. $payroll_entry["ss_wages_ytd"] . '</td></tr>';
					$childString .= '<tr><th>Deductions</th><td>$' . $payroll_entry["total_deductions"] .
										'</td><th>YTD</th><td>$'. $payroll_entry["total_deductions_ytd"] . '</td></tr>';

					$childString .= '</table>';

					
				$childString .= '</td></tr></table><br><hr width="850px" align="left"><br>';

			}
			
		}

$childString .= "<pre>".print_r($payroll_data,true)."</pre>";








//**************************************************************//
/*

		//Pull Payroll Configuration Information
		$payroll_config = df_get_record('_payroll_config', array('config_id'=>1));
		
		//Pull all Payroll Entries associated with this Payroll Period
		$payroll_entries = df_get_records_array("payroll_entries", array("payroll_period_id"=>$record->val('payroll_period_id')));

		foreach($payroll_entries as $entry){
			//Pull the Employee Record for the associated Employee
			$employee_record = df_get_record('employees',array('employee_id'=>$entry->val('employee_id')));
			$employee_name = $employee_record->val("first_name") . " " . $employee_record->val("last_name");
			$childString .= "<b><u>" . $employee_name . "</a></u></b>";
			
			//If the Payroll Period has not yet been closed, include a link to edit the entry.
			if($record->val("status") != "Closed")
				$childString .= " - <a href=index.php?-table=payroll_entries&-action=edit&-recordid=".$entry->getID()."&-review=" . $record->getID() . ">Edit this Entry</a><br><br>";

			//Indent the following sections
			$childString .= '<table><tr><td style="padding-left:15px; padding-right:15px; width: 600px;">';

			
			// ********** INCOME SECTION **********
			
				//Initialize Variables
				$total_income = 0;
				//$total_taxable_income = 0;
				$total_income_ytd = 0;
				$total_wages = 0;
				$total_ss_wages = 0;
				
				//Create Table
				$childString .= '<b><u>Income</u></b><br><br>';
				$childString .= '<table class="view_add"><tr>
									<th>Description</th>
									<th>Account</th>
									<th>Taxable</th>
									<th>Hours</th>
									<th>Amount Base</th>
									<th>Multiply</th>
									<th>Total</th>
									<th>YTD</th>
									</tr>';

				//Parse through all Income Records for the Payroll Entry
				$addition_records = df_get_records_array('payroll_entries_income',array('payroll_entry_id'=>$entry->val('payroll_entry_id')));
				foreach($addition_records as $addition_record){
					$subtotal = 0;
					$type_record = df_get_record('payroll_income_type',array('type_id'=>$addition_record->val('type')));

					$childString .= '<tr>';

					//Description
					$childString .= '<td>' . $type_record->val('name') . '</td>';

					//Account
					$account_record = df_get_record('chart_of_accounts',array('account_id'=>$type_record->val('account_number')));
					$childString .= '<td>' . $account_record->val('account_number') . " (" . $account_record->val('account_name') . ')</td>';

					//Taxable
					if($addition_record->val('taxable') == 1)
						$childString .= '<td>Yes</td>';
					else
						$childString .= '<td></td>';

					//Hours
					if($addition_record->val('hours') != null){
						//Check if type is Vacation Hours & if Negative make box show red
						if($addition_record->val('type') == $payroll_config->val('vacation_hours_type') && $employee_record->val('hours_remain_vacation') < $addition_record->val('hours') && $record->val('status') == null)
							$childString .= '<td style="background-color: red;">' . $addition_record->val('hours') . '</td>';
						else
							$childString .= '<td>' . $addition_record->val('hours') . '</td>';
					}
					else
						$childString .= '<td style="text-align: center">---</td>';

					//Amount - Base
					if($addition_record->val('amount_base') != null){
						$childString .= '<td style="text-align: right">$' . $addition_record->val('amount_base') . '</td>';
						$subtotal += $addition_record->val('amount_base');
					}
					else
						$childString .= '<td style="text-align: center">---</td>';

					//Amount - Multiply (by hours)
					if($addition_record->val('amount_multiply') != null){
						$childString .= '<td style="text-align: right">' . $addition_record->val('amount_multiply') . '</td>';
						$subtotal += $addition_record->val('hours') * $addition_record->val('amount_multiply');
					}
					else
						$childString .= '<td style="text-align: center">---</td>';
					
					//Total
					$childString .= '<td style="text-align: right">$' . number_format($subtotal,2) . '</td>';

					//Get YTD Amount - If already posted, use that amount, otherwise pull from the YTD Record
					//if($addition_record->val('posted_ytd') != null && $addition_record->val('posted_ytd') != 0){
					//	$ytd_amount = $addition_record->val('posted_ytd');
					//}
					//else{
					//	//Get YTD record / amount for the given Employee & Type
					//	$ytd_record = df_get_record("payroll_entries_income_ytd",array("employee_id"=>$employee_record->val("employee_id"),"type"=>$addition_record->val('type'),"year"=>">= " . date('Y-01-01') . " AND <=" . date('Y-12-31')));
					//	//$ytd_record = df_get_record("payroll_entries_income_ytd",array("employee_id"=>$employee_record->val("employee_id"),"type"=>$addition_record->val('type')));
					//	$ytd_amount = isset($ytd_record) ? $ytd_record->val('ytd_amount') : 0;
					//}

					//Show YTD Values - check to see if they have already been assigned to the entry (ie. has it been posted)
					//if($addition_record->val('posted_ytd') != null && $addition_record->val('posted_ytd') != 0)
					//	$childString .= '<td style="text-align: right">$' . number_format($ytd_amount, 2) . '</td>';
					//else
					//	$childString .= '<td style="text-align: right">$' . number_format($ytd_amount, 2) . " + $". number_format($subtotal, 2) .'</td>';


					//Show YTD Values - check to see if they have already been assigned to the entry (ie. has it been posted)
					if($addition_record->val('posted_ytd') != null && $addition_record->val('posted_ytd') != 0)
						$childString .= '<td style="text-align: right">$' . number_format($addition_record->val('posted_ytd'), 2) . '</td>';
					else
						$childString .= '<td style="text-align: right">---';
					
					$childString .= '</tr>';

					//Save income to total
					$total_income += round($subtotal,2);
					//if($addition_record->val('posted_ytd') != null && $addition_record->val('posted_ytd') != 0)
					//	$total_income_ytd += $ytd_amount;
					//else
					//	$total_income_ytd += $ytd_amount + $subtotal;
					
					//Save taxable income to taxable total
					if($addition_record->val('taxable') == 1){
					//	$total_taxable_income += round($subtotal,2);
						$total_ss_wages += round($subtotal,2);
					}

				} //End foreach
					
				//Pull all Pre-Tax Deductions, subtract from taxable income - Calculate Taxable Income for FICA / Medicare
				$addition_records = df_get_records_array('payroll_entries_deductions',array('payroll_entry_id'=>$entry->val('payroll_entry_id'),'pre_tax'=>1));
				foreach($addition_records as $addition_record){
					//Subtract from taxable income.
					//$total_taxable_income -= ($addition_record->val('amount_base') + ($total_income * $addition_record->val('amount_multiply')));
					$total_ss_wages -= ($addition_record->val('amount_base') + ($total_income * $addition_record->val('amount_multiply')));
					
					//Sanity check and fix
					//if($total_taxable_income < 0)
					//	$total_taxable_income = 0;
					if($total_ss_wages < 0)
						$total_ss_wages = 0;
				}

				//Total Wages is the Total SS Wages [minus the deductions (401K) that don't decrease SS wages]. Set equal and then subtract.
				$total_wages = $total_ss_wages;
				
				//Pull all 401K Deductions, subtract from social security wages - Calculate Taxable Income for Federal / State Taxes
				$addition_records = df_get_records_array('payroll_entries_deductions',array('payroll_entry_id'=>$entry->val('payroll_entry_id'),'type'=>$payroll_config->val('401k_deduction_type')));
				foreach($addition_records as $addition_record){
					//Subtract from taxable income.
//					$total_taxable_income -= ($addition_record->val('amount_base') + ($total_income * $addition_record->val('amount_multiply')));
					$total_wages -= ($addition_record->val('amount_base') + ($total_income * $addition_record->val('amount_multiply')));

					//Sanity check and fix
					if($total_wages < 0)
						$total_wages = 0;
				}

				$ytd_wages = 0;
				$ytd_ss_wages = 0;
				$ytd_net_income = 0;
				
				//Get all the payroll periods that count for the current YTD and add the employee income totals
				$payroll_periods = df_get_records_array('payroll_period',array('period_start'=>'>='.date('Y-01-01'),'period_start'=>'<='.date('Y-12-31')));
				foreach($payroll_periods as $payroll_period){
					$period_entry_record = df_get_record('payroll_entries',array('payroll_period_id'=>$payroll_period->val('payroll_period_id'),'employee_id'=>$entry->val('employee_id')));
					if($period_entry_record != null){
						//echo $payroll_period->val('payroll_period_id') . ' - ' . $period_entry_record->val('total_income') . '<br>';
						$ytd_wages += $period_entry_record->val('net_pay');
						$ytd_ss_wages += $period_entry_record->val('ss_wages');
						$ytd_gross_income += $period_entry_record->val('total_income');
					}
				}
				
				//End Table
				$childString .= '<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
				$childString .= '<tr><td><b>Total Income</b></td><td></td><td></td><td></td><td></td><td></td><td style="text-align: right"><b>$'.number_format($total_income,2).'</b></td><td style="text-align: right">$'.number_format($total_income_ytd,2).' / '.$ytd_gross_income.'</td></tr>';
				//$childString .= '<tr><td>Wages</td><td></td><td></td><td></td><td></td><td style="text-align: right">$'.number_format($total_taxable_income,2).'</td></tr>';
				$childString .= '<tr><td>Wages</td><td></td><td></td><td></td><td></td><td></td><td style="text-align: right">$'.number_format($total_wages,2).'</td><td>$'.$ytd_wages.'</td></tr>';
				$childString .= '<tr><td>Social Security Wages</td><td></td><td></td><td></td><td></td><td></td><td style="text-align: right">$'.number_format($total_ss_wages,2).'</td><td>$'.$ytd_ss_wages.'</td></tr></table>';

				
				//Save $total_taxable_income to the global taxable income
				//$GLOBALS['taxable_income'] = $total_taxable_income;


			// **** DEDUCTIONS SECTION ****

				//Initialize Variables
				$total_deductions = 0;
				$total_deductions_ytd = 0;
				
				//Start Table
				$childString .= '<br><br><b><u>Deductions</u></b><br><br>';
				$childString .= '<table class="view_add"><tr>
									<th>Description</th>
									<th>Account</th>
									<th>Pre Tax</th>
									<th>Amount Base</th>
									<th>Multiply</th>
									<th>Annual Limit</th>
									<th>Total</th>
									<th>YTD</th>
									</tr>';
									
				//Parse through all Deduction Records for the Payroll Entry
				$addition_records = df_get_records_array('payroll_entries_deductions',array('payroll_entry_id'=>$entry->val('payroll_entry_id')));
				foreach($addition_records as $addition_record){
					$subtotal = 0;
					$type_record = df_get_record('payroll_deductions_type',array('type_id'=>$addition_record->val('type')));
					
					$childString .= '<tr>';

					//Description
					$childString .= '<td>' . $type_record->val('name') . '</td>';

					//Account
					$account_record = df_get_record('chart_of_accounts',array('account_id'=>$type_record->val('account_number')));
					$childString .= '<td>' . $account_record->val('account_number') . " (" . $account_record->val('account_name') . ')</td>';

					//Pre Tax
					if($addition_record->val('pre_tax') == 1)
						$childString .= "<td>Yes</td>";
					else
						$childString .= "<td></td>";

					//Check if the payroll period has been posted
						
						
					//Amount - Base
						//Check if type is Federal Income Tax
						if($addition_record->val('type') == $payroll_config->val('federal_type')){
							//Exemption Amount - Where 'state' is FED = federal
							$exemption_record = df_get_record("_payroll_config_tax_exemptions",array("state"=>"FED"));
							$exemption_amount = $exemption_record->val($payroll_config->val('payroll_period')) * $employee_record->val('exemptions_federal');

							//Total taxable income minus exemptions
							//$taxable_income_minus_exemptions = $total_taxable_income - $exemption_amount;
							$taxable_income_minus_exemptions = $total_wages - $exemption_amount;

							//Calculate Income Tax - This function is from payroll_entries.php
							$subtotal = calculate_tax_table($taxable_income_minus_exemptions, $employee_record->val("marital_status"), "FED");

							//Check for modifications and add in
							if($addition_record->val('amount_base') != null)
								$subtotal += $addition_record->val('amount_base');
							
							//Display
							$childString .= '<td style="text-align: right">$' . $subtotal . '</td>';
						}
						//Check if type is State Income Tax
						elseif($addition_record->val('type') == $payroll_config->val('state_type')){
							//Exemption Amount - Where state is the state from the employee record
							$exemption_record = df_get_record("_payroll_config_tax_exemptions",array("state"=>$employee_record->val("state")));
//							$exemption_amount = $exemption_record->val($payroll_config->val('payroll_period')) * $employee_record->val('exemptions_state');

							//Total taxable income minus exemptions
							//$taxable_income_minus_exemptions = $total_taxable_income - $exemption_amount;
//							$taxable_income_minus_exemptions = $total_wages - $exemption_amount;

							//Calculate Income Tax - This function is from payroll_entries.php
//							$subtotal = calculate_tax_table($taxable_income_minus_exemptions, $employee_record->val("marital_status"), $employee_record->val("state"));

							//Check for modifications and add in
//							if($addition_record->val('amount_base') != null)
//								$subtotal += $addition_record->val('amount_base');
							
							//Display
							$childString .= '<td style="text-align: right">$' . $subtotal . '</td>';
						}
						//Check if null
						elseif($addition_record->val('amount_base') != null){
							$childString .= '<td style="text-align: right">$' . $addition_record->val('amount_base') . '</td>';
							$subtotal += $addition_record->val('amount_base');
						}
						else
							$childString .= '<td style="text-align: center">---</td>';

					//Amount - Multiply
					if($addition_record->val('amount_multiply') != null){
						$childString .= '<td style="text-align: right">' . $addition_record->val('amount_multiply') . '</td>';

						//If pre-tax, or type is "401K" (which handles kind of like pre-tax)
						if($addition_record->val('pre_tax') == 1 || $addition_record->val('type') == $payroll_config->val('401k_deduction_type'))
							$subtotal += $total_income * $addition_record->val('amount_multiply');
						//If type is "FICA" or "Medicare"
						elseif($addition_record->val('type') == $payroll_config->val('fica_deduction_type') || $addition_record->val('type') == $payroll_config->val('medicare_deduction_type'))
							//$subtotal += $total_taxable_income * $addition_record->val('amount_multiply');
							$subtotal += $total_ss_wages * $addition_record->val('amount_multiply');
						else //E.g. 401R (Roth)
							$subtotal += $total_wages * $addition_record->val('amount_multiply');
					}
					else
						$childString .= '<td style="text-align: center">---</td>';
					
					//Get YTD Amount - If already posted, use that amount, otherwise pull from the YTD Record
					if($addition_record->val('posted_ytd') != null && $addition_record->val('posted_ytd') != 0)
						$ytd_amount = $addition_record->val('posted_ytd');
					else{
						//Get YTD record / amount for the given Employee & Type
						$ytd_record = df_get_record("payroll_entries_deductions_ytd",array("employee_id"=>$employee_record->val("employee_id"),"type"=>$addition_record->val('type'),"year"=>date('Y')));
						$ytd_amount = isset($ytd_record) ? $ytd_record->val('ytd_amount') : 0;
					}

					//Annual Limit
						//Check for Annual Limits
						if($addition_record->val('annual_limit') != null){
							//Check to make sure employee hasn't yet paid the maximum amount for FICA tax - pay to max & then make $0.00;
								if($ytd_amount >= $addition_record->val('annual_limit'))
									$subtotal = 0.0;
								elseif($subtotal + $ytd_amount > $addition_record->val('annual_limit'))
										$subtotal = $addition_record->val('annual_limit') - $ytd_amount;

							$childString .= '<td style="text-align: right">' . $addition_record->val('annual_limit') . '</td>';
						}
						else
							$childString .= '<td style="text-align: center"> ---</td>';

	
					//Total
					$childString .= '<td style="text-align: right">$' . number_format($subtotal,2) . '</td>';

					//Show YTD Values - check to see if they have already been assigned to the entry (ie. has it been posted)
					if($addition_record->val('posted_ytd') != null && $addition_record->val('posted_ytd') != 0)
						$childString .= '<td style="text-align: right">$' . number_format($ytd_amount, 2) . '</td>';
					else
						$childString .= '<td style="text-align: right">$' . number_format($ytd_amount + $subtotal, 2) . '</td>';
					
					$childString .= '</tr>';
					
					//Save deductions to total
					$total_deductions += round($subtotal,2);
					if($addition_record->val('posted_ytd') != null && $addition_record->val('posted_ytd') != 0)
						$total_deductions_ytd += $ytd_amount;
					else
						$total_deductions_ytd += $ytd_amount + $subtotal;

				}
				
				//End Table
				$childString .= '<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
				$childString .= '<tr><td><b>Total Deductions</b></td><td></td><td></td><td></td><td></td><td></td><td><b>$' . number_format($total_deductions,2).'</b></td><td>$' . number_format($total_deductions_ytd,2) . '</td></tr></table>';


			// **** CONTRIBUTIONS SECTION ****
			
				//Start Table
				$childString .= '<br><br><b><u>Contributions</u></b><br><br>';
				$childString .= '<table class="view_add"><tr>
									<th>Description</th>
									<th>Account</th>
									<th>Amount Base</th>
									<th>Multiply</th>
									<th>Annual Limit</th>
									<th>Total</th>
									<th>YTD</th>
									</tr>';
									
				//Get taxable income from the global scope variable
				//$total_taxable_income = $GLOBALS['taxable_income'];
									
				//Parse through all Contribution Records for the Payroll Entry
				$addition_records = df_get_records_array('payroll_entries_contributions',array('payroll_entry_id'=>$entry->val('payroll_entry_id')));
				foreach($addition_records as $addition_record){
					//Initialize Variables
					$subtotal = 0;
					$type_record = df_get_record('payroll_contributions_type',array('type_id'=>$addition_record->val('type')));

				
					$childString .= '<tr>';
					
					//Description
					$childString .= '<td>' . $type_record->val('name') . '</td>';
					
					//Account
					$account_record = df_get_record('chart_of_accounts',array('account_id'=>$type_record->val('account_number')));
					$childString .= '<td>' . $account_record->val('account_number') . " (" . $account_record->val('account_name') . ')</td>';

					//Amount - Base
					if($addition_record->val('amount_base') != null){
						$childString .= '<td style="text-align: right">$' . $addition_record->val('amount_base') . '</td>';
						$subtotal += $addition_record->val('amount_base');
					}
					else
						$childString .= '<td style="text-align: center">---</td>';
						
					//Amount - Multiply
					if($addition_record->val('amount_multiply') != null){
						$childString .= '<td style="text-align: right">' . $addition_record->val('amount_multiply') . '</td>';
						//$subtotal += $total_taxable_income * $addition_record->val('amount_multiply');
						
						//If type is "401K"
						if($addition_record->val('type') == $payroll_config->val('401k_contribution_type'))
							$subtotal += $total_income * $addition_record->val('amount_multiply');
						//If type is "FICA" or "Medicare"
						elseif($addition_record->val('type') == $payroll_config->val('fica_contribution_type') || $addition_record->val('type') == $payroll_config->val('medicare_contribution_type'))
							//$subtotal += $total_taxable_income * $addition_record->val('amount_multiply');
							$subtotal += $total_ss_wages * $addition_record->val('amount_multiply');
						else //E.g. 401R (Roth)
							$subtotal += $total_wages * $addition_record->val('amount_multiply');
						
						
						
					}
					else
						$childString .= '<td style="text-align: center">---</td>';

					//Get YTD Amount - If already posted, use that amount, otherwise pull from the YTD Record
					if($addition_record->val('posted_ytd') != null && $addition_record->val('posted_ytd') != 0)
						$ytd_amount = $addition_record->val('posted_ytd');
					else{
						//Get YTD record / amount for the given Employee & Type
						$ytd_record = df_get_record("payroll_entries_contributions_ytd",array("employee_id"=>$employee_record->val("employee_id"),"type"=>$addition_record->val('type'),"year"=>date('Y')));
						$ytd_amount = isset($ytd_record) ? $ytd_record->val('ytd_amount') : 0;
					}

					//Annual Limit
						//Check for Annual Limits
						if($addition_record->val('annual_limit') != null){
							//Check to make sure employee hasn't yet paid the maximum amount for FICA tax - pay to max & then make $0.00;
								if($ytd_amount >= $addition_record->val('annual_limit'))
									$subtotal = 0.0;
								elseif($subtotal + $ytd_amount > $addition_record->val('annual_limit'))
										$subtotal = $addition_record->val('annual_limit') - $ytd_amount;

							$childString .= '<td style="text-align: right">' . $addition_record->val('annual_limit') . '</td>';
						}
						else
							$childString .= '<td style="text-align: center"> ---</td>';

					//Total
					$childString .= '<td style="text-align: right">$' . number_format($subtotal,2) . '</td>';
					
					//Show YTD Values - check to see if they have already been assigned to the entry (ie. has it been posted)
					if($addition_record->val('posted_ytd') != null && $addition_record->val('posted_ytd') != 0)
						$childString .= '<td style="text-align: right">$' . number_format($ytd_amount, 2) . '</td>';
					else
						$childString .= '<td style="text-align: right">$' . number_format($ytd_amount + $subtotal, 2) . '</td>';
					
					$childString .= '</tr>';					
				}
				
				//End Table
				$childString .= '</table><br><br>';


			//*** NET ***

			//This may end up in a $0.01 difference between the above totals due to rounding.
			$net_pay = $total_income - $total_deductions;

			//Get YTD records / amount for the given Employee
			//$ytd_record = df_get_record("payroll_entries_income_ytd",array("employee_id"=>$employee_record->val("employee_id")));
			
			$ytd_gross = $total_income_ytd;
			$ytd_net = $total_income_ytd - $total_deductions_ytd;
			//if(isset($ytd_record)){
			//	$ytd_gross += $ytd_record->val('ytd_gross');
			//	$ytd_net += $ytd_record->val('ytd_net');
			//}
			
			$childString .= '<td style="padding-left:15px; border-left:1px solid #000000; vertical-align:top;">';
				$childString .= '<table>';
				$childString .= '<th></th>';
				$childString .= '<th style="border-bottom: 1px solid #000000;">Current</th>';
				$childString .= '<th style="border-bottom: 1px solid #000000;">YTD</th>';
				$childString .= '<tr><td><b>Gross Income:</b></td><td style="text-align: right;">$' . number_format($total_income,2) . '</td><td style="text-align: right;">$' . number_format($ytd_gross,2) . '</td></tr>';
				$childString .= '<tr><td><b>Deductions:</b></td><td style="text-align: right;">$' . number_format($total_deductions,2) . '</td><td style="text-align: right;">$' . number_format($total_deductions_ytd,2) . '</td></tr>';
				$childString .= '<tr><td></td></tr>';
				$childString .= '<tr><td style="border-bottom: 1px solid #000000;"><b>Net Pay:</b></td><td style="border-bottom: 1px solid #000000; text-align: right;"><b>$' . number_format($net_pay,2) . '</b></td><td style="border-bottom: 1px solid #000000; text-align: right;">$' . number_format($ytd_net, 2) . '</td></tr>';
				$childString .= '</table>';
			$childString .= '</td>';

			$GLOBALS['totals_gross_income'] += $total_income;
			$GLOBALS['$totals_net_income'] += round($net_pay,2);
			$GLOBALS['$totals_deductions'] += $total_deductions;



				
				
				
				
			$childString .= '</td></tr></table>';
			$childString .= '<hr width="850px" align="left">';

		}
*/
//**************************************************************//
/*
		//Pull Payroll Configuration Information
		$payroll_config = df_get_record('_payroll_config', array('config_id'=>1));

		

			



*/		
		return array(
			'content' => "$childString",
			'class' => 'main',
			'label' => 'Payroll Entries',
			'order' => 10
		);
	}

	function block__before_record_actions(){
		echo "<u>Period Totals</u>";
		//echo "<table>";
		//echo '<tr><td>Gross Income:</td><td style="text-align: right;">$' . number_format($GLOBALS['totals_gross_income'],2) . "</td></tr>";
		//echo '<tr><td>Net Pay:</td><td style="text-align: right;">$' . number_format($GLOBALS['$totals_net_income'],2) . "</td></tr>";
		//echo '<tr><td>Deductions:</td><td style="text-align: right;">$' . number_format($GLOBALS['$totals_deductions'],2) . "</td></tr>";
		//echo "</table>";

	}

	//Create "Post" button, and perform the Post when pressed.
	function section__status(&$record){
		$app =& Dataface_Application::getInstance(); 
		$query =& $app->getQuery();
		$childString = '';

		//If the "Post" button has been pressed.
		//Because both the $_GET and $query will be "" on a new record, check to insure that they are not empty.
		if(($_GET['-status_post'] == $query['-recordid']) && ($query['-recordid'] != "") && $record->val("status") == ""){
			$msg = $this->post_payroll($record);

			$childString .= '<form name="status_post">';
			$childString .= '<input type="hidden" name="-table" value="'.$query['-table'].'">';
			$childString .= '<input type="hidden" name="-action" value="'.$query['-action'].'">';
			$childString .= '<input type="hidden" name="-recordid" value="'.$record->getID().'">';

			$childString .= '<input type="hidden" name="--msg" value="'.$msg.'">';;

			$childString .= '</form>';
			$childString .= '<script language="Javascript">document.status_post.submit();</script>';

		}
		else{
			$childString .= '<form>';
			$childString .= '<input type="hidden" name="-table" value="'.$query['-table'].'">';
			$childString .= '<input type="hidden" name="-action" value="'.$query['-action'].'">';
			$childString .= '<input type="hidden" name="-recordid" value="'.$record->getID().'">';
			$childString .= '<input type="hidden" name="-status_post" value="'.$record->getID().'">';

			if($record->val('status') == '')
				$childString .= '<input type="submit" value="Post Payroll">';
			else
				return array(); //Don't show section if already posted.

			$childString .= '</form>';
		}
		return array(
			'content' => "$childString",
			'class' => 'main',
			'label' => 'Status',
			'order' => 10
		);
	}

	
	function beforeSave(&$record){
	
	}

	function afterSave(&$record){
	}

	function beforeDelete(&$record){
		//Delete all Entries & Sub-Entries before deleting the main Payroll Period record.
		
		//Array of all the tables that entries will be deleted from
		$tables = array("payroll_entries","payroll_entries_income","payroll_entries_deductions","payroll_entries_contributions");
		
		//Loop through the assigned tables
		foreach($tables as $table){
			//Pull all entry records within the given Payroll Period
			$entries =& df_get_records_array($table,array("payroll_period_id"=>$record->val("payroll_period_id")));
			
			//Loop through each entry and delete it.
			foreach($entries as $entry)
				$entry->delete();
		}
		
	}

	//*****************************************************************************************//
	//*****************************************************************************************//
	//*****************************************************************************************//
	//*****************************************************************************************//
	//*****************************************************************************************//

	function payroll_from_entries($record){
		return "entries";
	}

	function calculate_payroll($record){

		//Initialize data
		$payroll_data = array();
		//$payroll_data['gross'] = 0;
		//$payroll_data['net'] = 0;
		//$payroll_data['deductions'] = 0;
		//$payroll_data['contributions'] = 0;
		
		//Pull Payroll Configuration Information
		$payroll_config = df_get_record('_payroll_config', array('config_id'=>1));

		//Determine year - for YTD
		if($payroll_config->val("ytd_date_option") == "period_start")
			$year = strtotime($record->strval("period_start"));
		elseif($payroll_config->val("ytd_date_option") == "period_end")
			$year = strtotime($record->strval("period_end"));

		$payroll_data["year"] = $year;
			
		//Pull all Payroll Entries associated with this Payroll Period & go through each.
		$payroll_entries = df_get_records_array("payroll_entries", array("payroll_period_id"=>$record->val('payroll_period_id')));
		foreach($payroll_entries as $e_key=>$entry){
			//Pull the Employee Record for the associated Employee
			$employee_record = df_get_record('employees',array('employee_id'=>$entry->val('employee_id')));

			$payroll_data[$e_key]["employee_id"] = $entry->val('employee_id');
			$payroll_data[$e_key]["employee_name"] = $employee_record->val("first_name") . " " . $employee_record->val("last_name");
			$payroll_data[$e_key]["entry_id"] = $entry->val("payroll_entry_id");
			$payroll_data[$e_key]["entry_id_url"] = $entry->getID();
			
			//Initialize Variables
			$payroll_data[$e_key]["gross_income"] = 0;
			$payroll_data[$e_key]["wages"] = 0;
			$payroll_data[$e_key]["ss_wages"] = 0;
			$payroll_data[$e_key]["total_deductions"] = 0;
			$payroll_data[$e_key]["total_contributions"] = 0;
			$payroll_data[$e_key]["gross_income_ytd"] = 0;
			$payroll_data[$e_key]["wages_ytd"] = 0;
			$payroll_data[$e_key]["ss_wages_ytd"] = 0;
			$payroll_data[$e_key]["total_deductions_ytd"] = 0;

			// ********** INCOME SECTION **********

				//Parse through all Income Records for the Payroll Entry
				$addition_records = df_get_records_array('payroll_entries_income',array('payroll_entry_id'=>$entry->val('payroll_entry_id')));
				foreach($addition_records as $a_key=>$addition_record){
					$subtotal = 0;
					
					//ID
					$payroll_data[$e_key]["income"]["income_entry_".$a_key]["record_id"] = $addition_record->val('payroll_income_id');
					
					//Pull the "type" record for the current entry
					$type_record = df_get_record('payroll_income_type',array('type_id'=>$addition_record->val('type')));
					$payroll_data[$e_key]["income"]["income_entry_".$a_key]["type"] = $addition_record->val('type');

					//Description
					$payroll_data[$e_key]["income"]["income_entry_".$a_key]["description"] = $type_record->val('name');

					//Account
					$account_record = df_get_record('chart_of_accounts',array('account_id'=>$type_record->val('account_number')));
					$payroll_data[$e_key]["income"]["income_entry_".$a_key]["account"] = $account_record->val('account_number') . " (" . $account_record->val('account_name') . ')';

					//Taxable
					if($addition_record->val('taxable') == 1)
						$payroll_data[$e_key]["income"]["income_entry_".$a_key]["taxable"] = 'Yes';
					else
						$payroll_data[$e_key]["income"]["income_entry_".$a_key]["taxable"] = '---';

					//Hours
					if($addition_record->val('hours') != null){
						//Check if type is Vacation Hours & if Negative make box show red
						if($addition_record->val('type') == $payroll_config->val('vacation_hours_type') && $employee_record->val('hours_remain_vacation') < $addition_record->val('hours') && $record->val('status') == null)
							$payroll_data[$e_key]["income"]["income_entry_".$a_key]["hours"] = '<div style="background-color: red;">' . $addition_record->val('hours') . '</div>';
						else
							$payroll_data[$e_key]["income"]["income_entry_".$a_key]["hours"] = $addition_record->val('hours');
					}
					else
						$payroll_data[$e_key]["income"]["income_entry_".$a_key]["hours"] = "---";

					//Amount - Base
					if($addition_record->val('amount_base') != null){
						$payroll_data[$e_key]["income"]["income_entry_".$a_key]["base"] = $addition_record->val('amount_base');
						$subtotal += $addition_record->val('amount_base');
					}
					else
						$payroll_data[$e_key]["income"]["income_entry_".$a_key]["base"] = "---";

					//Amount - Multiply (by hours)
					if($addition_record->val('amount_multiply') != null){
						$payroll_data[$e_key]["income"]["income_entry_".$a_key]["multiply"] = $addition_record->val('amount_multiply');
						$subtotal += $addition_record->val('hours') * $addition_record->val('amount_multiply');
					}
					else
						$payroll_data[$e_key]["income"]["income_entry_".$a_key]["multiply"] = "---";
					
					//Total
					$payroll_data[$e_key]["income"]["income_entry_".$a_key]["total_amount"] = number_format($subtotal,2);
					
					//Get YTD Amount - pull from the YTD Record - for the given Employee & Type
					$ytd_record = df_get_record("payroll_entries_income_ytd",array("employee_id"=>$employee_record->val("employee_id"),"type"=>$addition_record->val('type'),"year"=>">= " . date('Y-01-01',$year) . " AND <=" . date('Y-12-31',$year)));
					$ytd_amount = isset($ytd_record) ? number_format($ytd_record->val('ytd_amount'),2) : null;
					
					//YTD
					$payroll_data[$e_key]["income"]["income_entry_".$a_key]["ytd"] = $ytd_amount;
					$payroll_data[$e_key]["income"]["income_entry_".$a_key]["ytd_record_id"] = isset($ytd_record) ? $ytd_record->val("payroll_income_ytd_id") : null;

					//Save income to total
					$payroll_data[$e_key]["gross_income"] += round($subtotal,2);

					//Save taxable income to taxable total
					if($addition_record->val('taxable') == 1){
						$payroll_data[$e_key]["ss_wages"] += round($subtotal,2);
					}

				} //End foreach

				//Pull all Pre-Tax Deductions, subtract from taxable income - Calculate Taxable Income for FICA / Medicare
				$addition_records = df_get_records_array('payroll_entries_deductions',array('payroll_entry_id'=>$entry->val('payroll_entry_id'),'pre_tax'=>1));
				foreach($addition_records as $addition_record){
					//Subtract from taxable income.
					$payroll_data[$e_key]["ss_wages"] -= ($addition_record->val('amount_base') + ($total_income * $addition_record->val('amount_multiply')));
					
					//Sanity check and fix
					if($payroll_data[$e_key]["ss_wages"] < 0)
						$payroll_data[$e_key]["ss_wages"] = 0;
				}

				//Total Wages is the Total SS Wages [minus the deductions (401K) that don't decrease SS wages]. Set equal and then subtract.
				$payroll_data[$e_key]["wages"] = $payroll_data[$e_key]["ss_wages"];

				//Pull all 401K Deductions, subtract from social security wages - Calculate Taxable Income for Federal / State Taxes
				$addition_records = df_get_records_array('payroll_entries_deductions',array('payroll_entry_id'=>$entry->val('payroll_entry_id'),'type'=>$payroll_config->val('401k_deduction_type')));
				foreach($addition_records as $addition_record){
					//Subtract from taxable income.
					$payroll_data[$e_key]["wages"] -= ($addition_record->val('amount_base') + ($payroll_data[$e_key]["gross_income"] * $addition_record->val('amount_multiply')));

					//Sanity check and fix
					if($payroll_data[$e_key]["wages"] < 0)
						$payroll_data[$e_key]["wages"] = 0;
				}

				//Save $total_taxable_income to the global taxable income
				//$GLOBALS['taxable_income'] = $total_taxable_income;


			// **** DEDUCTIONS SECTION ****

				//Initialize Variables
				//$total_deductions = 0;
				//$total_deductions_ytd = 0;
				
				//Parse through all Deduction Records for the Payroll Entry
				$addition_records = df_get_records_array('payroll_entries_deductions',array('payroll_entry_id'=>$entry->val('payroll_entry_id')));
				foreach($addition_records as $a_key=>$addition_record){
					$subtotal = 0;
					
					//ID
					$payroll_data[$e_key]["deductions"]["deduction_entry_".$a_key]["record_id"] = $addition_record->val('payroll_deduction_id');

					//Pull the "type" record for the current entry
					$type_record = df_get_record('payroll_deductions_type',array('type_id'=>$addition_record->val('type')));
					$payroll_data[$e_key]["deductions"]["deduction_entry_".$a_key]["type"] = $addition_record->val('type');

					//Description
					$payroll_data[$e_key]["deductions"]["deduction_entry_".$a_key]["description"] = $type_record->val('name');

					//Account
					$account_record = df_get_record('chart_of_accounts',array('account_id'=>$type_record->val('account_number')));
					$payroll_data[$e_key]["deductions"]["deduction_entry_".$a_key]["account"] = $account_record->val('account_number') . " (" . $account_record->val('account_name') . ')';

					//Pre Tax
					if($addition_record->val('pre_tax') == 1)
						$payroll_data[$e_key]["deductions"]["deduction_entry_".$a_key]["pre_tax"] = 'Yes';
					else
						$payroll_data[$e_key]["deductions"]["deduction_entry_".$a_key]["pre_tax"] = '---';

					//Amount - Base
						//Check if type is Federal Income Tax
						if($addition_record->val('type') == $payroll_config->val('federal_type')){
							//Exemption Amount - Where 'state' is FED = federal
							$exemption_record = df_get_record("_payroll_config_tax_exemptions",array("state"=>"FED"));
							$exemption_amount = $exemption_record->val($payroll_config->val('payroll_period')) * $employee_record->val('exemptions_federal');

							//Total taxable income minus exemptions
							$taxable_income_minus_exemptions = $payroll_data[$e_key]["wages"] - $exemption_amount;

							//Calculate Income Tax - This function is from payroll_entries.php
							$subtotal = calculate_tax_table($taxable_income_minus_exemptions, $employee_record->val("marital_status"), "FED");

							//Check for modifications and add in
							if($addition_record->val('amount_base') != null)
								$subtotal += $addition_record->val('amount_base');
							
							//Total
							$payroll_data[$e_key]["deductions"]["deduction_entry_".$a_key]["base"] = number_format($subtotal,2);
						}
						//Check if type is State Income Tax
						elseif($addition_record->val('type') == $payroll_config->val('state_type')){
							//Exemption Amount - Where state is the state from the employee record
							$exemption_record = df_get_record("_payroll_config_tax_exemptions",array("state"=>$employee_record->val("state")));
							
							//If record does not exist exemption amount = 0, else get from database.
							if($exemption_record == null)
								$exemption_amount = 0;
							else
								$exemption_amount = $exemption_record->val($payroll_config->val('payroll_period')) * $employee_record->val('exemptions_state');

							//Total taxable income minus exemptions
							$taxable_income_minus_exemptions = $payroll_data[$e_key]["wages"] - $exemption_amount;

							//Calculate Income Tax - This function is from payroll_entries.php
							$subtotal = calculate_tax_table($taxable_income_minus_exemptions, $employee_record->val("marital_status"), $employee_record->val("state"));

							//Check for modifications and add in
							if($addition_record->val('amount_base') != null)
								$subtotal += $addition_record->val('amount_base');
							
							//Total
							$payroll_data[$e_key]["deductions"]["deduction_entry_".$a_key]["base"] = number_format($subtotal,2);
						}
						//Check if null
						elseif($addition_record->val('amount_base') != null){
							//Pull from record
							$subtotal += $addition_record->val('amount_base');
							
							//Total
							$payroll_data[$e_key]["deductions"]["deduction_entry_".$a_key]["base"] = number_format($subtotal,2);
						}
						else
							$payroll_data[$e_key]["deductions"]["deduction_entry_".$a_key]["base"] = "---";

					//Amount - Multiply
					if($addition_record->val('amount_multiply') != null){
						$payroll_data[$e_key]["deductions"]["deduction_entry_".$a_key]["multiply"] = $addition_record->val('amount_multiply');

						//If pre-tax, or type is "401K" (which handles kind of like pre-tax)
						if($addition_record->val('pre_tax') == 1 || $addition_record->val('type') == $payroll_config->val('401k_deduction_type'))
							$subtotal += $payroll_data[$e_key]["gross_income"] * $addition_record->val('amount_multiply');
						//If type is "FICA" or "Medicare"
						elseif($addition_record->val('type') == $payroll_config->val('fica_deduction_type') || $addition_record->val('type') == $payroll_config->val('medicare_deduction_type'))
							//$subtotal += $total_taxable_income * $addition_record->val('amount_multiply');
							$subtotal += $payroll_data[$e_key]["ss_wages"] * $addition_record->val('amount_multiply');
						else //E.g. 401R (Roth)
							$subtotal += $payroll_data[$e_key]["wages"] * $addition_record->val('amount_multiply');
					}
					else
						$payroll_data[$e_key]["deductions"]["deduction_entry_".$a_key]["multiply"] = "---";
					
					//Get YTD Amount - pull from the YTD Record - for the given Employee & Type
					$ytd_record = df_get_record("payroll_entries_deductions_ytd",array("employee_id"=>$employee_record->val("employee_id"),"type"=>$addition_record->val('type'),"year"=>">= " . date('Y-01-01',$year) . " AND <=" . date('Y-12-31',$year)));
					$ytd_amount = isset($ytd_record) ? number_format($ytd_record->val('ytd_amount'),2) : null;
					
					//YTD
					$payroll_data[$e_key]["deductions"]["deduction_entry_".$a_key]["ytd"] = $ytd_amount;
					$payroll_data[$e_key]["deductions"]["deduction_entry_".$a_key]["ytd_record_id"] = isset($ytd_record) ? $ytd_record->val("payroll_deduction_ytd_id") : null;

					//Annual Limit
						//Check for Annual Limits
						if($addition_record->val('annual_limit') != null){
							//Check to make sure employee hasn't yet paid the maximum amount for FICA tax - pay to max & then make $0.00;
								if($ytd_amount >= $addition_record->val('annual_limit'))
									$subtotal = 0.0;
								elseif($subtotal + $ytd_amount > $addition_record->val('annual_limit'))
										$subtotal = $addition_record->val('annual_limit') - $ytd_amount;

							$payroll_data[$e_key]["deductions"]["deduction_entry_".$a_key]["annual_limit"] = number_format($addition_record->val('annual_limit'),2);
						}
						else
							$payroll_data[$e_key]["deductions"]["deduction_entry_".$a_key]["annual_limit"] = "---";

	
					//Total
					$payroll_data[$e_key]["deductions"]["deduction_entry_".$a_key]["total_amount"] = number_format($subtotal,2);

					//Save deductions to total
					$payroll_data[$e_key]["total_deductions"] += round($subtotal,2);

				}


			// **** CONTRIBUTIONS SECTION ****
			
				//Parse through all Contribution Records for the Payroll Entry
				$addition_records = df_get_records_array('payroll_entries_contributions',array('payroll_entry_id'=>$entry->val('payroll_entry_id')));
				foreach($addition_records as $a_key=>$addition_record){
					//Initialize Variables
					$subtotal = 0;

					//ID
					$payroll_data[$e_key]["contributions"]["contribution_entry_".$a_key]["record_id"] = $addition_record->val('payroll_contribution_id');

					//Pull the "type" record for the current entry
					$type_record = df_get_record('payroll_contributions_type',array('type_id'=>$addition_record->val('type')));
					$payroll_data[$e_key]["contributions"]["contribution_entry_".$a_key]["type"] = $addition_record->val('type');

					//Description
					$payroll_data[$e_key]["contributions"]["contribution_entry_".$a_key]["description"] = $type_record->val('name');

					//Account - Liability & Expense
					$account_record = df_get_record('chart_of_accounts',array('account_id'=>$type_record->val('account_number_liability')));
					$payroll_data[$e_key]["contributions"]["contribution_entry_".$a_key]["account_liability"] = $account_record->val('account_number') . " (" . $account_record->val('account_name') . ')';

					$account_record = df_get_record('chart_of_accounts',array('account_id'=>$type_record->val('account_number_expense')));
					$payroll_data[$e_key]["contributions"]["contribution_entry_".$a_key]["account_expense"] = $account_record->val('account_number') . " (" . $account_record->val('account_name') . ')';
					
					//Amount - Base
					if($addition_record->val('amount_base') != null){
						$payroll_data[$e_key]["contributions"]["contribution_entry_".$a_key]["base"] = $addition_record->val('amount_base');
						$subtotal += $addition_record->val('amount_base');
					}
					else
						$payroll_data[$e_key]["contributions"]["contribution_entry_".$a_key]["base"] = "---";
						
					//Amount - Multiply
					if($addition_record->val('amount_multiply') != null){
						$payroll_data[$e_key]["contributions"]["contribution_entry_".$a_key]["multiply"] = $addition_record->val('amount_multiply');
						
						//If type is "401K"
						if($addition_record->val('type') == $payroll_config->val('401k_contribution_type'))
							$subtotal += $payroll_data[$e_key]["gross_income"] * $addition_record->val('amount_multiply');
						//If type is "FICA" or "Medicare"
						elseif($addition_record->val('type') == $payroll_config->val('fica_contribution_type') || $addition_record->val('type') == $payroll_config->val('medicare_contribution_type'))
							//$subtotal += $total_taxable_income * $addition_record->val('amount_multiply');
							$subtotal += $payroll_data[$e_key]["ss_wages"] * $addition_record->val('amount_multiply');
						else //E.g. 401R (Roth)
							$subtotal += $payroll_data[$e_key]["wages"] * $addition_record->val('amount_multiply');
					}
					else
						$payroll_data[$e_key]["contributions"]["contribution_entry_".$a_key]["multiply"] = "---";

					//Get YTD Amount - pull from the YTD Record - for the given Employee & Type
					$ytd_amount = isset($ytd_record) ? number_format($ytd_record->val('ytd_amount'),2) : null;
					$ytd_record = df_get_record("payroll_entries_contributions_ytd",array("employee_id"=>$employee_record->val("employee_id"),"type"=>$addition_record->val('type'),"year"=>">= " . date('Y-01-01',$year) . " AND <=" . date('Y-12-31',$year)));
					
					//YTD
					$payroll_data[$e_key]["contributions"]["contribution_entry_".$a_key]["ytd"] = $ytd_amount;
					$payroll_data[$e_key]["contributions"]["contribution_entry_".$a_key]["ytd_record_id"] = isset($ytd_record) ? $ytd_record->val("payroll_contribution_ytd_id") : null;

					//Annual Limit
						//Check for Annual Limits
						if($addition_record->val('annual_limit') != null){
							//Check to make sure employee hasn't yet paid the maximum amount for FICA tax - pay to max & then make $0.00;
								if($ytd_amount >= $addition_record->val('annual_limit'))
									$subtotal = 0.0;
								elseif($subtotal + $ytd_amount > $addition_record->val('annual_limit'))
										$subtotal = $addition_record->val('annual_limit') - $ytd_amount;

							$payroll_data[$e_key]["contributions"]["contribution_entry_".$a_key]["annual_limit"] = number_format($addition_record->val('annual_limit'),2);
						}
						else
							$payroll_data[$e_key]["contributions"]["contribution_entry_".$a_key]["annual_limit"] = "---";

					//Total
					$payroll_data[$e_key]["contributions"]["contribution_entry_".$a_key]["total_amount"] = number_format($subtotal,2);

					//Save contributions to total
					$payroll_data[$e_key]["total_contributions"] += round($subtotal,2);
					
				}

			// **** YTD ****
			
			//Pull all Payroll Periods that have already been posted & are within the YTD - based on the YTD configuration option.
			if($payroll_config->val("ytd_date_option") == "period_start")
				$payroll_periods_posted = df_get_records_array("payroll_period", array("status"=>"Posted","period_start"=>">= " . date('Y-01-01',$year) . " AND <=" . date('Y-12-31',$year)));
			elseif($payroll_config->val("ytd_date_option") == "period_end")
				$payroll_periods_posted = df_get_records_array("payroll_period", array("status"=>"Posted","period_start"=>">= " . date('Y-01-01',$year) . " AND <=" . date('Y-12-31',$year)));

			//Loop through Payroll Periods and pull out data from the entry assigned to the given employee in each.
			foreach($payroll_periods_posted as $payroll_period_posted){
				$payroll_period_entry = df_get_record("payroll_entries", array("payroll_period_id"=>$payroll_period_posted->val("payroll_period_id"),"employee_id"=>$entry->val('employee_id')));
				
				$payroll_data[$e_key]["gross_income_ytd"] += $payroll_period_entry->val("gross_income");
				$payroll_data[$e_key]["wages_ytd"] += $payroll_period_entry->val("wages");
				$payroll_data[$e_key]["ss_wages_ytd"] += $payroll_period_entry->val("ss_wages");
				$payroll_data[$e_key]["total_deductions_ytd"] += $payroll_period_entry->val("total_deductions");
				$payroll_data[$e_key]["total_contributions_ytd"] += $payroll_period_entry->val("total_contributions");
			}
			
			//Add current period to YTD
			$payroll_data[$e_key]["gross_income_ytd"] += $payroll_data[$e_key]["gross_income"];
			$payroll_data[$e_key]["wages_ytd"] += $payroll_data[$e_key]["wages"];
			$payroll_data[$e_key]["ss_wages_ytd"] += $payroll_data[$e_key]["ss_wages"];
			$payroll_data[$e_key]["total_deductions_ytd"] += $payroll_data[$e_key]["total_deductions"];
			$payroll_data[$e_key]["total_contributions_ytd"] += $payroll_data[$e_key]["total_contributions"];
		}

	return $payroll_data;

	}

	function post_payroll($record){
		/*		Set Status to "Posted"
					Payroll Period
					All Payroll Period Entries
					All Payroll Period Income / Deductions / Contributions Entries

				Increment all YTD values
					Save to payroll_entry_XXX & payroll_entry_XXX_ytd
				- Decrement Vacation / Sick hours
		
				All payroll entries non-editable
				Create general ledger journal entries
					Debit: Wage Expense Account (Income / Contributions Expense)
					Credit: All Associated Liability Accounts (Deductions / Contributions Payable)
		*/

			//Set "Payroll Period" status to Posted, & "Posted Date" to Today's Date.
			$record->setValue('status',"Posted");
			$record->setValue('post_date',date("Y-m-d"));
//			$res = $record->save(null, true); //Save w/ permission check

			$save_error = 0;
			
			//Check for errors.
			if ( PEAR::isError($res) ){
				// An error occurred
				$save_error = 1;
				///throw new Exception($res->getMessage());
				$msg = "Unable to Post Payroll. This is most likely because you do not have the required permissions.";
			}
			else
				$msg = "Payroll Period [" . $record->val("payroll_period_id"). "] has been posted.";
		
			//If there was an error, don't continue, otherwise do.
			if($save_error == 0){

				//Pull Payroll Configuration Information
				$payroll_config = df_get_record('_payroll_config', array('config_id'=>1));
				
				//Create an empty array for Journal Entries
				$journal_entry = array();

				//Get Payroll Data
				$payroll_data = $this->calculate_payroll($record);
				$year = $payroll_data["year"];

				//Traverse through all data in the payroll_data array
				foreach($payroll_data as $payroll_entry){
					//Check to see if the entry is an array (if not ignore)
					if(is_array($payroll_entry)){
					
						//Pull Employee Record
						$employee_record = df_get_record("employees", array("employee_id"=>$payroll_entry["employee_id"]));
						
						//Pull Entry Record
						$payroll_entry_record = df_get_record("payroll_entries", array("payroll_entry_id"=>$payroll_entry["entry_id"]));
						
						//Save Entry Record Data
						$payroll_entry_record->setValues(array(
							'status'=>"Posted",
							'gross_income'=>$payroll_entry["gross_income"],
							'wages'=>$payroll_entry["wages"],
							'ss_wages'=>$payroll_entry["ss_wages"],
							'total_deductions'=>$payroll_entry["total_deductions"],
							'total_contributions'=>$payroll_entry["total_contributions"],
							'gross_income_ytd'=>$payroll_entry["gross_income_ytd"],
							'wages_ytd'=>$payroll_entry["wages_ytd"],
							'ss_wages_ytd'=>$payroll_entry["ss_wages_ytd"],
							'total_deductions_ytd'=>$payroll_entry["total_deductions_ytd"]
						));
//						$res = $payroll_entry_record->save(); //Save w/o permission check - presumably if the first check passed, the user has permission to post payroll

						//Parse through all Income, Deduction, and Contribution Entries
						$addition_types = array("income","deductions","contributions");
						foreach($addition_types as $addition_type){
							$addition_type_single = (substr($addition_type,-1) == "s") ? substr($addition_type,0,-1) : $addition_type; //Remove the 's' from "deductions" & "contributions"
						
							foreach($payroll_entry[$addition_type] as $addition_entry){
								//Check if YTD record exists 
								if($addition_entry["ytd_record_id"] == null){ //If not
									//YTD Total
									$ytd_amount = $addition_entry['total_amount'];
									
									//Create a new record
									$addition_record_ytd = new Dataface_Record('payroll_entries_' . $addition_type . '_ytd', array());
									$addition_record_ytd->setValues(array(
										'employee_id'=>$payroll_entry['employee_id'],
										'type'=>$addition_entry['type'],
										'year'=>$year
									));
								}
								else{ //If so
									//Pull existing record
									$addition_record_ytd = df_get_record('payroll_entries_' . $addition_type . '_ytd', array('payroll_' . $addition_type_single .'_ytd_id'=>$addition_entry["ytd_record_id"]));

									//Calculate YTD Total
									$ytd_amount = $addition_record_ytd->val('ytd_amount') + $addition_entry['total_amount'];
								}

								//Pull entry record
								$addition_record = df_get_record("payroll_entries_" . $addition_type,array("payroll_" . $addition_type_single . "_id"=>$addition_entry['record_id']));
								
								//Save "Total" & "YTD Total" to the entry record, & "YTD Total" to the associated YTD Record
								$addition_record->setValue('posted_amount', $addition_entry['total_amount']);
								$addition_record->setValue('posted_ytd', $ytd_total);
								$addition_record_ytd->setValue('ytd_amount', $ytd_total);

								//Set Status to Posted
								$addition_record->setValue('status', "Posted");

								//Save Record & YTD Record
//								$res = $addition_record->save(); //Save w/o permission check - presumably if the first check passed, the user has permission to post payroll
//								$res = $addition_record_ytd->save(); //Save w/o permission check - presumably if the first check passed, the user has permission to post payroll

								//Check if entry type is "income" - If so, check for & process Vacation / Sick hours
								if($addition_type == "income"){
									//Check if type is Vacation Hours
									if($addition_entry['type'] == $payroll_config->val('vacation_hours_type')){
										//Decrement vacation hours from employee
										$hours_remain = $employee_record->val('hours_remain_vacation') - $addition_entry['hours'];
										$employee_record->setValue('hours_remain_vacation', $hours_remain);
//										$res = $employee_record->save(); //Save w/o permission check - presumably if the first check passed, the user has permission to post payroll
									}
									//Check if type is Sick Hours
									if($addition_entry['type'] == $payroll_config->val('sick_hours_type')){
										//Decrement vacation hours from employee
										$hours_remain = $employee_record->val('hours_remain_sick') - $addition_entry['hours'];
										$employee_record->setValue('hours_remain_vacation', $hours_remain);
//										$res = $employee_record->save(); //Save w/o permission check - presumably if the first check passed, the user has permission to post payroll
									}
								}

								//Add to GL Journal
								if($addition_type == "income"){ //-> Income = Debit Associated Account
									$journal_entry[$addition_record->val('account_number')]['debit'] += $addition_entry['total_amount'];
								}
								elseif($addition_type == "deductions"){ //-> Deductions = Credit Associated Account
									$journal_entry[$addition_record->val('account_number')]['credit'] += $addition_entry['total_amount'];
								}
								elseif($addition_type == "contributions"){ //-> Expense Account = Debit Account, Liability = Credit Account
									$journal_entry[$addition_record->val('account_number_expense')]['debit'] += $addition_entry['total_amount'];
									$journal_entry[$addition_record->val('account_number_liability')]['credit'] += $addition_entry['total_amount'];
								}

							}
						}
					}
				}

/*
					//Pull all Contribution Records for the Payroll Entry
					$addition_records = df_get_records_array('payroll_entries_contributions',array('payroll_entry_id'=>$entry->val('payroll_entry_id')));
					foreach($addition_records as $addition_record){
						//Get associated YTD Record - Otherwise, create one.
							$addition_record_ytd = df_get_record('payroll_entries_contributions_ytd', array('employee_id'=>$entry->val('employee_id'),'type'=>$addition_record->val('type'),'year'=>date('Y')));

							$ytd_total = 0;
							if(isset($addition_record_ytd)){
								$ytd_total = $addition_record_ytd->val("ytd_amount");
							}
							else{
								$addition_record_ytd = new Dataface_Record('payroll_entries_contributions_ytd', array());
								$addition_record_ytd->setValues(array(
									'employee_id'=>$entry->val('employee_id'),
									'type'=>$addition_record->val('type'),
									'year'=>date("Y")
								));
							}
						
						//Calculate "Total" - (income_total * multiply + base)
							$record_total = $entry_total_taxable_income * $addition_record->val('amount_multiply') + $addition_record->val('amount_base');

						//Check for Annual Limits
						if($addition_record->val('annual_limit') != null){
							//Check to make sure employee hasn't yet paid the maximum amount
							if($ytd_total >= $addition_record->val('annual_limit'))
								$record_total = 0.0;
							elseif($record_total + $ytd_total > $addition_record->val('annual_limit'))
								$record_total = $addition_record->val('annual_limit') - $ytd_total;
						}


						//Add Current to YTD Amount
							$ytd_total += $record_total;

						//Add "Total" & "YTD Total" to this record, & "YTD Total" to the associated YTD Record
							$addition_record->setValue('posted_amount', $record_total);
							$addition_record->setValue('posted_ytd', $ytd_total);
							$addition_record_ytd->setValue('ytd_amount', $ytd_total);

						//Set Status to Posted
							$addition_record->setValue('status', "Posted");

						//Save Record & YTD Record
							$res = $addition_record->save(); //Save w/o permission check - presumably if the first check passed, the user has permission to post payroll
							$res = $addition_record_ytd->save(); //Save w/o permission check - presumably if the first check passed, the user has permission to post payroll
					}

					//Add Total Income, Deductions, and Net Pay to Record
					$entry->setValue('total_income',$entry_total_gross_income);
					$entry->setValue('total_deductions',$entry_total_deductions);
					$entry->setValue('net_pay',($entry_total_gross_income - $entry_total_deductions));

					//Set "Payroll Entry" status to Posted
					$entry->setValue('status',"Posted");
					
					//Save Record
					$res = $entry->save(); //Save w/o permission check - presumably if the first check passed, the user has permission to post payroll

				}
				
				//Create General Ledger & Journal Entries
				$gl_record = new Dataface_Record('general_ledger', array());
				$gl_record->setValues(array(
					'ledger_date'=>date("Y-m-d"),
					'ledger_description'=>"Payroll Period #".$record->val('payroll_period_id')
				));

				//Save Record
				$res = $gl_record->save(); //Save w/o permission check - presumably if the first check passed, the user has permission to post payroll

				//Parse through all journal entries and save
				foreach($journal_entry as $key => $entry){
					$glj_record = new Dataface_Record('general_ledger_journal', array());
					$glj_record->setValues(array(
						'ledger_id'=>$gl_record->val('ledger_id'),
						'date'=>date("Y-m-d"),
						'account_id'=>$key,
						'debit'=>isset($entry['debit']) ? $entry['debit'] : null,
						'credit'=>isset($entry['credit']) ? $entry['credit'] : null
					));
					
					//Save Record
					$res = $glj_record->save(); //Save w/o permission check - presumably if the first check passed, the user has permission to post payroll
				}

				//Create Net Pay Journal Entry
				$glj_record = new Dataface_Record('general_ledger_journal', array());
				$glj_record->setValues(array(
					'ledger_id'=>$gl_record->val('ledger_id'),
					'date'=>date("Y-m-d"),
					'account_id'=>$payroll_config->val('wages_payable'),
					'credit'=>$GLOBALS['$totals_net_income']
				));
					
				//Save Record
				$res = $glj_record->save(); //Save w/o permission check - presumably if the first check passed, the user has permission to post payroll
*/
			}

		return $msg;
	}
	
}







/*
//Calculate the federal/state/city tax using the tax tables - City as yet to be implemented
function calculate_tax_table($taxable_income_minus_exemptions, $marital_status, $state = '='){

	$payroll_config = df_get_record('_payroll_config', array('config_id'=>1));
	$payroll_period = $payroll_config->val('payroll_period');
	
	$tax_table = df_get_records_array('_payroll_config_tax_tables', array('state'=>$state));

	//Figure out which section to use when calculating federal taxes
	switch(array($payroll_period,$marital_status)){
		case array('weekly','S'):
			$tax_status = "single_weekly"; break;
		case array('weekly','M'):
			$tax_status = "married_weekly"; break;
		case array('2weeks','S'):
			$tax_status = "single_2weeks"; break;
		case array('2weeks','M'):
			$tax_status = "married_2weeks"; break;
	}

	$total_tax = 0;
	
	//Parse through the tax brackets calculating
	foreach($tax_table as $key => $tax_bracket){
					
		//If we have gone past the maximum tax bracket for the given income, stop
		if($taxable_income_minus_exemptions <= $tax_bracket->val($tax_status))
			break;

						//If there are still brackets above the one we're on...
						if(isset($tax_table[$key+1])){
							//If taxable income is above the next bracket - calculate up to the next bracket
							if($taxable_income_minus_exemptions >= $tax_table[$key+1]->val($tax_status))
								$total_tax += ($tax_table[$key+1]->val($tax_status) - $tax_bracket->val($tax_status)) * $tax_bracket->val("percent");
							else //Calculate only the portion in this bracket
								$total_tax += ($taxable_income_minus_exemptions - $tax_bracket->val($tax_status)) * $tax_bracket->val("percent");
						}
						else //Do all the rest of the taxable income
							$total_tax += ($taxable_income_minus_exemptions - $tax_bracket->val($tax_status)) * $tax_bracket->val("percent");
//echo $state . ": " . $tax_bracket->val($tax_status) . ": " . $total_tax . "<br>";
	}
	return $total_tax;
}
*/


/*				
					
				//Pull all Payroll Entries associated with this Payroll Period
				$payroll_entries = df_get_records_array("payroll_entries", array("payroll_period_id"=>$record->val('payroll_period_id')));
				foreach($payroll_entries as $entry){
					//Reset Variables
					$entry_total_gross_income = 0;
					$entry_total_wages = 0;
					$entry_total_ss_wages = 0;
					$entry_total_deductions = 0;

					//Get Employee Record Information
					$employee_record = df_get_record("employees", array('employee_id'=>$entry->val('employee_id')));

					//Pull all Income Records for the Payroll Entry
					$addition_records = df_get_records_array('payroll_entries_income',array('payroll_entry_id'=>$entry->val('payroll_entry_id')));
					foreach($addition_records as $addition_record){
						//Get associated YTD Record - Otherwise, create one.
							$addition_record_ytd = df_get_record('payroll_entries_income_ytd', array('employee_id'=>$entry->val('employee_id'),'type'=>$addition_record->val('type'),'year'=>">= " . date('Y-01-01',$year) . " AND <=" . date('Y-12-31',$year)));
							
							$ytd_total = 0;
							if(isset($addition_record_ytd)){
								$ytd_total = $addition_record_ytd->val("ytd_amount");
							}
							else{
								$addition_record_ytd = new Dataface_Record('payroll_entries_income_ytd', array());
								$addition_record_ytd->setValues(array(
									'employee_id'=>$entry->val('employee_id'),
									'type'=>$addition_record->val('type'),
									'year'=>date("Y")
								));
							}

						//Calculate "Total" & round to 2 decimals - (hours * multiply + base)
							$record_total = round($addition_record->val('hours') * $addition_record->val('amount_multiply') + $addition_record->val('amount_base'),2);

						//Add to GL Journal -> Income = Debit Associated Account
						$journal_entry[$addition_record->val('account_number')]['debit'] += $record_total;//round($record_total,2);
							
						//Add Current to YTD Amount
							$ytd_total += $record_total;

						//Add "Total" & "YTD Total" to this record, & "YTD Total" to the associated YTD Record
							$addition_record->setValue('posted_amount', $record_total);
							$addition_record->setValue('posted_ytd', $ytd_total);
							$addition_record_ytd->setValue('ytd_amount', $ytd_total);

						//Add "Total" to running income_total
							$entry_total_gross_income += $record_total;

						//Set Status to Posted
							$addition_record->setValue('status', "Posted");

						//Save Record & YTD Record
							$res = $addition_record->save(); //Save w/o permission check - presumably if the first check passed, the user has permission to post payroll
							$res = $addition_record_ytd->save(); //Save w/o permission check - presumably if the first check passed, the user has permission to post payroll

						//Check if type is Vacation Hours
						if($addition_record->val('type') == $payroll_config->val('vacation_hours_type')){
							//Decrement vacation hours from employee
							$hours_remain = $employee_record->val('hours_remain_vacation') - $addition_record->val('hours');
							$employee_record->setValue('hours_remain_vacation', $hours_remain);
							$res = $employee_record->save(); //Save w/o permission check - presumably if the first check passed, the user has permission to post payroll
						}
						//Check if type is Sick Hours
						if($addition_record->val('type') == $payroll_config->val('sick_hours_type')){
							//Decrement vacation hours from employee
							$hours_remain = $employee_record->val('hours_remain_sick') - $addition_record->val('hours');
							$employee_record->setValue('hours_remain_vacation', $hours_remain);
							$res = $employee_record->save(); //Save w/o permission check - presumably if the first check passed, the user has permission to post payroll
						}
					}

					//Pull all Pre-Tax Deductions, subtract from taxable income.
					$addition_records = df_get_records_array('payroll_entries_deductions',array('payroll_entry_id'=>$entry->val('payroll_entry_id'),'pre_tax'=>1));
					foreach($addition_records as $addition_record){
						//Subtract from taxable income.  --- Multiplication probably shouldn't happen, as it could potentially make things a bit weird, and should maybe not be used. Using Gross Income for multiply, as using Taxable Income would potentially lead to awkwardness, reducing itself (semi-recursive style).
						$entry_total_ss_wages -= ($addition_record->val('amount_base') + ($entry_total_gross_income * $addition_record->val('amount_multiply')));

						//Sanity check and fix
						if($entry_total_taxable_income < 0)
							$entry_total_taxable_income = 0;
					}

					
					
					
		//Pull all Pre-Tax Deductions, subtract from taxable income - Calculate Taxable Income for FICA / Medicare
		$addition_records = df_get_records_array('payroll_entries_deductions',array('payroll_entry_id'=>$entry->val('payroll_entry_id'),'pre_tax'=>1));
		foreach($addition_records as $addition_record){
			//Subtract from taxable income.
			$payroll_data[$e_key]["ss_wages"] -= ($addition_record->val('amount_base') + ($total_income * $addition_record->val('amount_multiply')));
					
			//Sanity check and fix
			if($payroll_data[$e_key]["ss_wages"] < 0)
				$payroll_data[$e_key]["ss_wages"] = 0;
		}

		//Total Wages is the Total SS Wages [minus the deductions (401K) that don't decrease SS wages]. Set equal and then subtract.
		$payroll_data[$e_key]["wages"] = $payroll_data[$e_key]["ss_wages"];

		//Pull all 401K Deductions, subtract from social security wages - Calculate Taxable Income for Federal / State Taxes
		$addition_records = df_get_records_array('payroll_entries_deductions',array('payroll_entry_id'=>$entry->val('payroll_entry_id'),'type'=>$payroll_config->val('401k_deduction_type')));
		foreach($addition_records as $addition_record){
			//Subtract from taxable income.
			$payroll_data[$e_key]["wages"] -= ($addition_record->val('amount_base') + ($payroll_data[$e_key]["gross_income"] * $addition_record->val('amount_multiply')));

			//Sanity check and fix
			if($payroll_data[$e_key]["wages"] < 0)
				$payroll_data[$e_key]["wages"] = 0;
		}
					
					
					
					
					
					
					
					
					
					
					//Pull all Deduction Records for the Payroll Entry
					$addition_records = df_get_records_array('payroll_entries_deductions',array('payroll_entry_id'=>$entry->val('payroll_entry_id')));
					foreach($addition_records as $addition_record){
						//Get associated YTD Record - Otherwise, create one.
							$addition_record_ytd = df_get_record('payroll_entries_deductions_ytd', array('employee_id'=>$entry->val('employee_id'),'type'=>$addition_record->val('type'),'year'=>date('Y')));

							$ytd_total = 0;
							if(isset($addition_record_ytd)){
								$ytd_total = $addition_record_ytd->val("ytd_amount");
							}
							else{
								$addition_record_ytd = new Dataface_Record('payroll_entries_deductions_ytd', array());
								$addition_record_ytd->setValues(array(
									'employee_id'=>$entry->val('employee_id'),
									'type'=>$addition_record->val('type'),
									'year'=>date("Y")
								));
							}
						
						//Calculate "Total" - (income_total * multiply + base)
							$record_total = $entry_total_taxable_income * $addition_record->val('amount_multiply') + $addition_record->val('amount_base');

						//Check if type is Federal Income Tax
						if($addition_record->val('type') == $payroll_config->val('federal_type')){
							//Exemption Amount - Where 'state' is FED = federal
							$exemption_record = df_get_record("_payroll_config_tax_exemptions",array("state"=>"FED"));
							$exemption_amount = $exemption_record->val($payroll_config->val('payroll_period')) * $employee_record->val('exemptions_federal');

							//Total taxable income minus exemptions
							$taxable_income_minus_exemptions = $entry_total_taxable_income - $exemption_amount;

							//Calculate Income Tax - This function is from payroll_entries.php
							$record_total += calculate_tax_table($taxable_income_minus_exemptions, $employee_record->val("marital_status"), "FED");
							
						}
						//Check if type is State Income Tax
						elseif($addition_record->val('type') == $payroll_config->val('state_type')){
							//Exemption Amount - Where state is the state from the employee record
							$exemption_record = df_get_record("_payroll_config_tax_exemptions",array("state"=>$employee_record->val("state")));
							$exemption_amount = $exemption_record->val($payroll_config->val('payroll_period')) * $employee_record->val('exemptions_state');

							//Total taxable income minus exemptions
							$taxable_income_minus_exemptions = $entry_total_taxable_income - $exemption_amount;

							//Calculate Income Tax - This function is from payroll_entries.php
							$record_total += calculate_tax_table($taxable_income_minus_exemptions, $employee_record->val("marital_status"), $employee_record->val("state"));
						}	

						//Check for Annual Limits
						if($addition_record->val('annual_limit') != null){
							//Check to make sure employee hasn't yet paid the maximum amount
							if($ytd_total >= $addition_record->val('annual_limit'))
								$record_total = 0.0;
							elseif($record_total + $ytd_total > $addition_record->val('annual_limit'))
								$record_total = $addition_record->val('annual_limit') - $ytd_total;
						}

						//Add to GL Journal -> Deductions = Credit Associated Account
						$journal_entry[$addition_record->val('account_number')]['credit'] += round($record_total,2);

						//Add Current to YTD Amount
							$ytd_total += $record_total;

						//Add "Total" & "YTD Total" to this record, & "YTD Total" to the associated YTD Record
							$addition_record->setValue('posted_amount', $record_total);
							$addition_record->setValue('posted_ytd', $ytd_total);
							$addition_record_ytd->setValue('ytd_amount', $ytd_total);
						
						//Add "Total" to running deductions_total
							$entry_total_deductions += $record_total;

						//Set Status to Posted
							$addition_record->setValue('status', "Posted");

						//Save Record & YTD Record
							$res = $addition_record->save(); //Save w/o permission check - presumably if the first check passed, the user has permission to post payroll
							$res = $addition_record_ytd->save(); //Save w/o permission check - presumably if the first check passed, the user has permission to post payroll
					}

					//Pull all Contribution Records for the Payroll Entry
					$addition_records = df_get_records_array('payroll_entries_contributions',array('payroll_entry_id'=>$entry->val('payroll_entry_id')));
					foreach($addition_records as $addition_record){
						//Get associated YTD Record - Otherwise, create one.
							$addition_record_ytd = df_get_record('payroll_entries_contributions_ytd', array('employee_id'=>$entry->val('employee_id'),'type'=>$addition_record->val('type'),'year'=>date('Y')));

							$ytd_total = 0;
							if(isset($addition_record_ytd)){
								$ytd_total = $addition_record_ytd->val("ytd_amount");
							}
							else{
								$addition_record_ytd = new Dataface_Record('payroll_entries_contributions_ytd', array());
								$addition_record_ytd->setValues(array(
									'employee_id'=>$entry->val('employee_id'),
									'type'=>$addition_record->val('type'),
									'year'=>date("Y")
								));
							}
						
						//Calculate "Total" - (income_total * multiply + base)
							$record_total = $entry_total_taxable_income * $addition_record->val('amount_multiply') + $addition_record->val('amount_base');

						//Check for Annual Limits
						if($addition_record->val('annual_limit') != null){
							//Check to make sure employee hasn't yet paid the maximum amount
							if($ytd_total >= $addition_record->val('annual_limit'))
								$record_total = 0.0;
							elseif($record_total + $ytd_total > $addition_record->val('annual_limit'))
								$record_total = $addition_record->val('annual_limit') - $ytd_total;
						}

						//Add to GL Journal -> Expense Account = Debit Account, Liability = Credit Account
						$journal_entry[$addition_record->val('account_number_expense')]['debit'] += round($record_total,2);
						$journal_entry[$addition_record->val('account_number_liability')]['credit'] += round($record_total,2);

						//Add Current to YTD Amount
							$ytd_total += $record_total;

						//Add "Total" & "YTD Total" to this record, & "YTD Total" to the associated YTD Record
							$addition_record->setValue('posted_amount', $record_total);
							$addition_record->setValue('posted_ytd', $ytd_total);
							$addition_record_ytd->setValue('ytd_amount', $ytd_total);

						//Set Status to Posted
							$addition_record->setValue('status', "Posted");

						//Save Record & YTD Record
							$res = $addition_record->save(); //Save w/o permission check - presumably if the first check passed, the user has permission to post payroll
							$res = $addition_record_ytd->save(); //Save w/o permission check - presumably if the first check passed, the user has permission to post payroll
					}

					//Add Total Income, Deductions, and Net Pay to Record
					$entry->setValue('total_income',$entry_total_gross_income);
					$entry->setValue('total_deductions',$entry_total_deductions);
					$entry->setValue('net_pay',($entry_total_gross_income - $entry_total_deductions));

					//Set "Payroll Entry" status to Posted
					$entry->setValue('status',"Posted");
					
					//Save Record
					$res = $entry->save(); //Save w/o permission check - presumably if the first check passed, the user has permission to post payroll

				}
				
				//Create General Ledger & Journal Entries
				$gl_record = new Dataface_Record('general_ledger', array());
				$gl_record->setValues(array(
					'ledger_date'=>date("Y-m-d"),
					'ledger_description'=>"Payroll Period #".$record->val('payroll_period_id')
				));

				//Save Record
				$res = $gl_record->save(); //Save w/o permission check - presumably if the first check passed, the user has permission to post payroll

				//Parse through all journal entries and save
				foreach($journal_entry as $key => $entry){
					$glj_record = new Dataface_Record('general_ledger_journal', array());
					$glj_record->setValues(array(
						'ledger_id'=>$gl_record->val('ledger_id'),
						'date'=>date("Y-m-d"),
						'account_id'=>$key,
						'debit'=>isset($entry['debit']) ? $entry['debit'] : null,
						'credit'=>isset($entry['credit']) ? $entry['credit'] : null
					));
					
					//Save Record
					$res = $glj_record->save(); //Save w/o permission check - presumably if the first check passed, the user has permission to post payroll
				}

				//Create Net Pay Journal Entry
				$glj_record = new Dataface_Record('general_ledger_journal', array());
				$glj_record->setValues(array(
					'ledger_id'=>$gl_record->val('ledger_id'),
					'date'=>date("Y-m-d"),
					'account_id'=>$payroll_config->val('wages_payable'),
					'credit'=>$GLOBALS['$totals_net_income']
				));
					
				//Save Record
				$res = $glj_record->save(); //Save w/o permission check - presumably if the first check passed, the user has permission to post payroll
*/


?>


