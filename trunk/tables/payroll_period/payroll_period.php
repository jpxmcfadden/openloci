<?php

class tables_payroll_period {

	function getTitle(&$record){
		return "Payroll Period: " . $record->strval('period_start').' to '.$record->strval('period_end');
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
			$childString .= " - <a href=index.php?-table=payroll_entries&-action=browse&-recordid=".$entry->getID()."&-review=" . $record->getID() . ">Edit this Entry</a><br><br>";

			//Indent the following sections
			$childString .= '<table><tr><td style="padding-left:15px; padding-right:15px;">';

			// **** INCOME SECTION ****
			
				//Initialize Variables
				$total_income = 0;
				$total_taxable_income = 0;
				
				//Create Table
				$childString .= '<b><u>Income</u></b><br><br>';
				$childString .= '<table class="view_add"><tr>
									<th>Description</th>
									<th>Taxable</th>
									<th>Hours</th>
									<th>Amount Base</th>
									<th>Multiply</th>
									<th>Total (net)</th>
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
					if($addition_record->val('hours') != null)
						$childString .= '<td>' . $addition_record->val('hours') . '</td>';
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
					$childString .= '<td style="text-align: right">$' . number_format($subtotal,4) . '</td>';

					$childString .= '</tr>';

					//Save income to total
					$total_income += $subtotal;
					
					//Save taxable income to taxable total
					if($addition_record->val('taxable') == 1)
						$total_taxable_income += $subtotal;

				} //End foreach

				//Pull all Pre-Tax Deductions, subtract from taxable income.
				$addition_records = df_get_records_array('payroll_entries_deductions',array('payroll_entry_id'=>$record->val('payroll_entry_id'),'pre_tax'=>1));
				foreach($addition_records as $addition_record){
					//Subtract from taxable income.
					$total_taxable_income -= ($addition_record->val('amount_base') + ($total_income * $addition_record->val('amount_multiply')));
					
					//Sanity check and fix
					if($total_taxable_income < 0)
						$total_taxable_income = 0;
				}
				
				//End Table
				$childString .= '<tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
				$childString .= '<tr><td><b>Total Income</b></td><td></td><td></td><td></td><td></td><td style="text-align: right"><b>$'.number_format($total_income,2).'</b></td></tr>';
				$childString .= '<tr><td>Taxable</td><td></td><td></td><td></td><td></td><td style="text-align: right">$'.number_format($total_taxable_income,2).'</td></tr></table>';

				
				//Save $total_taxable_income to the global taxable income
				//$GLOBALS['taxable_income'] = $total_taxable_income;


			// **** DEDUCTIONS SECTION ****

				//Initialize Variables
				$total_deductions = 0;
				
				//Start Table
				$childString .= '<br><br><b><u>Deductions</u></b><br><br>';
				$childString .= '<table class="view_add"><tr>
									<th>Description</th>
									<th>Pre Tax</th>
									<th>Amount Base</th>
									<th>Amount Percent</th>
									<th>Total</th>
									</tr>';
									
				//Parse through all Deduction Records for the Payroll Entry
				$addition_records = df_get_records_array('payroll_entries_deductions',array('payroll_entry_id'=>$entry->val('payroll_entry_id')));
				foreach($addition_records as $addition_record){
					$subtotal = 0;
					$type_record = df_get_record('payroll_deductions_type',array('type_id'=>$addition_record->val('type')));
					
					$childString .= '<tr>';

					//Description
					$childString .= '<td>' . $type_record->val('name') . '</td>';

					//Post Tax
					if($addition_record->val('pre_tax') == 1)
						$childString .= "<td>Yes</td>";
					else
						$childString .= "<td></td>";

					//Amount - Base
						//Check if type is Federal Income Tax
						if($addition_record->val('type') == $payroll_config->val('federal_type')){
							//Exemption Amount - Where state is null = federal
							$exemption_record = df_get_record("_payroll_config_tax_exemptions",array("state"=>"="));
							$exemption_amount = $exemption_record->val($payroll_config->val('payroll_period')) * $employee_record->val('exemptions_federal');

							//Total taxable income minus exemptions
							$taxable_income_minus_exemptions = $total_taxable_income - $exemption_amount;

							//Calculate Income Tax - This function is from payroll_entries.php
							$subtotal = calculate_tax_table($taxable_income_minus_exemptions, $employee_record->val("marital_status"));

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
							$taxable_income_minus_exemptions = $total_taxable_income - $exemption_amount;

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
						//if($addition_record->val('pre_tax') == 1)
						//	$subtotal += $total_income * $addition_record->val('amount_multiply');
						//else
							$subtotal += $total_taxable_income * $addition_record->val('amount_multiply');
					}
					else
						$childString .= '<td style="text-align: center">---</td>';
					
					//Total
					$childString .= '<td style="text-align: right">$' . number_format($subtotal,4) . '</td>';

					$childString .= '</tr>';
					
					//Save deductions to total
					$total_deductions += $subtotal;
				}
				
				//End Table
				$childString .= '<tr><td></td><td></td><td></td><td></td><td></td></tr>';
				$childString .= '<tr><td><b>Total Deductions</b></td><td></td><td></td><td></td><td><b>$' . number_format($total_deductions,2).'</b></td></tr></table>';


			// **** CONTRIBUTIONS SECTION ****
			
				//Start Table
				$childString .= '<br><br><b><u>Contributions</u></b><br><br>';
				$childString .= '<table class="view_add"><tr>
									<th>Description</th>
									<th>Amount Base</th>
									<th>Amount Percent</th>
									<th>Total</th>
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
						$subtotal += $total_taxable_income * $addition_record->val('amount_multiply');
					}
					else
						$childString .= '<td style="text-align: center">---</td>';

					$childString .= '<td style="text-align: right">$' . number_format($subtotal,4) . '</td>';
					$childString .= '</tr>';
				}
				
				//End Table
				$childString .= '</table><br><br>';


			//*** NET ***
			$childString .= '<td style="padding-left:15px; border-left:1px solid #000000; vertical-align:top;">';
				//This may end up in a $0.01 difference between the above totals due to rounding.
				//$childString .= '<br><br>';
				$childString .= '<table>';
				$childString .= '<tr><td><b>Gross Income:</b></td><td>$' . number_format($total_income,2) . "</td></tr>";
				$childString .= '<tr><td><b>Deductions:</b></td><td>$' . number_format($total_deductions,2) . "</td></tr>";
				$childString .= '<tr><td></td></tr>';
				$childString .= '<tr><td style="border-bottom: 1px solid #000000;"><b>Net Pay:</b></td><td style="border-bottom: 1px solid #000000;">$' . number_format(($total_income) - ($total_deductions),2) . "</td></tr>";
				$childString .= '</table>';
			$childString .= '</td>';





				
				
				
				
			$childString .= '</td></tr></table>';
			$childString .= '<hr width="60%" align="left">';

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
		echo "Totals:";

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


