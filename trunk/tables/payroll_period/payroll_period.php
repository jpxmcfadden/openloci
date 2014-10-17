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

			// **** INCOME SECTION ****
			
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
					if($addition_record->val('posted_ytd') != null && $addition_record->val('posted_ytd') != 0)
						$ytd_amount = $addition_record->val('posted_ytd');
					else{
						//Get YTD record / amount for the given Employee & Type
						$ytd_record = df_get_record("payroll_entries_income_ytd",array("employee_id"=>$employee_record->val("employee_id"),"type"=>$addition_record->val('type'),"year"=>date('Y')));
						$ytd_amount = isset($ytd_record) ? $ytd_record->val('ytd_amount') : 0;
					}

					//Show YTD Values - check to see if they have already been assigned to the entry (ie. has it been posted)
					if($addition_record->val('posted_ytd') != null && $addition_record->val('posted_ytd') != 0)
						$childString .= '<td style="text-align: right">$' . number_format($ytd_amount, 2) . '</td>';
					else
						$childString .= '<td style="text-align: right">$' . number_format($ytd_amount + $subtotal, 2) . '</td>';
				
					$childString .= '</tr>';

					//Save income to total
					$total_income += round($subtotal,2);
					if($addition_record->val('posted_ytd') != null && $addition_record->val('posted_ytd') != 0)
						$total_income_ytd += $ytd_amount;
					else
						$total_income_ytd += $ytd_amount + $subtotal;
					
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
				$payroll_periods = df_get_records_array('payroll_period',array('period_start'=>'>=2014-01-01','period_start'=>'<=2014-12-31'));
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
				$childString .= '<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
				$childString .= '<tr><td><b>Total Income</b></td><td></td><td></td><td></td><td></td><td style="text-align: right"><b>$'.number_format($total_income,2).'</b></td><td style="text-align: right">$'.number_format($total_income_ytd,2).' / '.$ytd_gross_income.'</td></tr>';
				//$childString .= '<tr><td>Wages</td><td></td><td></td><td></td><td></td><td style="text-align: right">$'.number_format($total_taxable_income,2).'</td></tr>';
				$childString .= '<tr><td>Wages</td><td></td><td></td><td></td><td></td><td style="text-align: right">$'.number_format($total_wages,2).'</td><td>$'.$ytd_wages.'</td></tr>';
				$childString .= '<tr><td>Social Security Wages</td><td></td><td></td><td></td><td></td><td style="text-align: right">$'.number_format($total_ss_wages,2).'</td><td>$'.$ytd_ss_wages.'</td></tr></table>';

				
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

					//Pre Tax
					if($addition_record->val('pre_tax') == 1)
						$childString .= "<td>Yes</td>";
					else
						$childString .= "<td></td>";

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
							$exemption_amount = $exemption_record->val($payroll_config->val('payroll_period')) * $employee_record->val('exemptions_state');

							//Total taxable income minus exemptions
							//$taxable_income_minus_exemptions = $total_taxable_income - $exemption_amount;
							$taxable_income_minus_exemptions = $total_wages - $exemption_amount;

							//Calculate Income Tax - This function is from payroll_entries.php
							$subtotal = calculate_tax_table($taxable_income_minus_exemptions, $employee_record->val("marital_status"), $employee_record->val("state"));

							//Check for modifications and add in
							if($addition_record->val('amount_base') != null)
								$subtotal += $addition_record->val('amount_base');
							
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
				$childString .= '<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
				$childString .= '<tr><td><b>Total Deductions</b></td><td></td><td></td><td></td><td></td><td><b>$' . number_format($total_deductions,2).'</b></td><td>$' . number_format($total_deductions_ytd,2) . '</td></tr></table>';


			// **** CONTRIBUTIONS SECTION ****
			
				//Start Table
				$childString .= '<br><br><b><u>Contributions</u></b><br><br>';
				$childString .= '<table class="view_add"><tr>
									<th>Description</th>
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
		echo "<table>";
		echo '<tr><td>Gross Income:</td><td style="text-align: right;">$' . number_format($GLOBALS['totals_gross_income'],2) . "</td></tr>";
		echo '<tr><td>Net Pay:</td><td style="text-align: right;">$' . number_format($GLOBALS['$totals_net_income'],2) . "</td></tr>";
		echo '<tr><td>Deductions:</td><td style="text-align: right;">$' . number_format($GLOBALS['$totals_deductions'],2) . "</td></tr>";
		echo "</table>";

	}

	//Create "Post" button, and perform the Post when pressed.
	function section__status(&$record){
		$app =& Dataface_Application::getInstance(); 
		$query =& $app->getQuery();
		$childString = '';

		//If the "Post" button has been pressed.
		//Because both the $_GET and $query will be "" on a new record, check to insure that they are not empty.
		if(($_GET['-status_post'] == $query['-recordid']) && ($query['-recordid'] != "") && $record->val("status") == ""){
		
		/*		Set Status to "Posted"
					Payroll Period
					All Payroll Period Entries
					All Payroll Period Income / Deductions / Contributions Entries

				Increment all YTD values
					Save to payroll_entry_XXX & payroll_entry_XXX_ytd
				- Decrement Vacation hours
		
				All payroll entries non-editable
				Create general ledger journal entries
					Debit: Wage Expense Account (Income / Contributions Expense)
					Credit: All Associated Liability Accounts (Deductions / Contributions Payable)
		*/

			//Set "Payroll Period" status to Posted, & "Posted Date" to Today's Date.
			$record->setValue('status',"Posted");
			$record->setValue('post_date',date("Y-m-d"));
			$res = $record->save(null, true); //Save w/ permission check

			$save_error = 0;
			
			//Check for errors.
			if ( PEAR::isError($res) ){
				// An error occurred
				$save_error = 1;
				///throw new Exception($res->getMessage());
				$msg = '<input type="hidden" name="--error" value="Unable to Post Payroll. This is most likely because you do not have the required permissions.">';
			}
			else
				$msg = '<input type="hidden" name="--msg" value="Payroll Period [' . $record->val("payroll_period_id"). '] has been posted.">';
		
			//If there was an error, don't continue, otherwise do.
			if($save_error == 0){

				//Pull Payroll Configuration Information
				$payroll_config = df_get_record('_payroll_config', array('config_id'=>1));
				
				//Pull all Payroll Entries associated with this Payroll Period
				$payroll_entries = df_get_records_array("payroll_entries", array("payroll_period_id"=>$record->val('payroll_period_id')));
				foreach($payroll_entries as $entry){
					//Reset Variables
					$entry_total_gross_income = 0;
					//$entry_total_taxable_income = 0;
					$entry_total_deductions = 0;
					//$entry_total_contributions = 0;
					//$entry_total_net_pay = 0;
					$entry_total_wages = 0;
					$entry_total_ss_wages = 0;

					//Get Employee Record Information
					$employee_record = df_get_record("employees", array('employee_id'=>$entry->val('employee_id')));

					//Pull all Income Records for the Payroll Entry
					$addition_records = df_get_records_array('payroll_entries_income',array('payroll_entry_id'=>$entry->val('payroll_entry_id')));
					foreach($addition_records as $addition_record){
						//Get associated YTD Record - Otherwise, create one.
							$addition_record_ytd = df_get_record('payroll_entries_income_ytd', array('employee_id'=>$entry->val('employee_id'),'type'=>$addition_record->val('type'),'year'=>date('Y')));

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

						//Calculate "Total" - (hours * multiply + base)
							$record_total = $addition_record->val('hours') * $addition_record->val('amount_multiply') + $addition_record->val('amount_base');

						//Add to GL Journal -> Income = Debit Associated Account
						$journal_entry[$addition_record->val('account_number')]['debit'] += round($record_total,2);
							
						//Add Current to YTD Amount
							$ytd_total += $record_total;

						//Add "Total" & "YTD Total" to this record, & "YTD Total" to the associated YTD Record
							$addition_record->setValue('posted_amount', $record_total);
							$addition_record->setValue('posted_ytd', $ytd_total);
							$addition_record_ytd->setValue('ytd_amount', $ytd_total);

						//Add "Total" to running income_total
							$entry_total_gross_income += $record_total;
							if($addition_record->val('taxable') != null)
								$entry_total_taxable_income += $record_total;

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
						$entry_total_taxable_income -= ($addition_record->val('amount_base') + ($entry_total_gross_income * $addition_record->val('amount_multiply')));

						//Sanity check and fix
						if($entry_total_taxable_income < 0)
							$entry_total_taxable_income = 0;
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

			}
			
			$childString .= '<form name="status_post">';
			$childString .= '<input type="hidden" name="-table" value="'.$query['-table'].'">';
			$childString .= '<input type="hidden" name="-action" value="'.$query['-action'].'">';
			$childString .= '<input type="hidden" name="-recordid" value="'.$record->getID().'">';

			$childString .= $msg;

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

?>


