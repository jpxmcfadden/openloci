<?php

//Use a global scope variable for taxable income so that the contributions section function can access it.
$taxable_income = 0;

class tables_payroll_entries {

	function getTitle(&$record){
		$employee_record = df_get_record('employees', array("employee_id"=>$record->val("employee_id")));
		$payroll_period_record = df_get_record('payroll_period', array("payroll_period_id"=>$record->val("payroll_period_id")));
			
		return $employee_record->val('first_name').' '.$employee_record->val('last_name').' - Payroll Period: '. $payroll_period_record->strval('period_start');;
	}

	//Permissions
	function getPermissions(&$record){
		//First check if the user is logged in.
		if( isUser() ){
			//Check status, determine if record should be uneditable.
			if ( isset($record) ){
				if(	$record->val('status') == "Closed")
					return Dataface_PermissionsTool::getRolePermissions('NO_EDIT_DELETE');
			}
			return Dataface_PermissionsTool::getRolePermissions(myRole());
		}
		else
			return Dataface_PermissionsTool::NO_ACCESS();
	}

	//If the record has been set to "Posted" don't allow the income and deduction form fields to be edited.
	function init(&$table){
		$app =& Dataface_Application::getInstance(); 
		$record =& $app->getRecord();

		if($record->val("status") ==  "Posted"){
		
			$myfield =& $table->getField('income');
			$myfield['widget']['type'] = 'hidden';
			
			$myfield =& $table->getField('deductions');
			$myfield['widget']['type'] = 'hidden';
		}
	}	
	
	//If coming from Payroll Review -	1) set a session variable which will be used to determine where to go in after_action_edit(),
	//									2) if coming from the review page & the record has been saved, go back to the review page.
	function block__before_form(){
		if(isset($_GET['--saved']) && isset($_GET['-review'])){
		//	echo $_GET['-review'];
			$msg = "Entry successfully saved.";
			header('Location: index.php?-action=browse&-table=payroll_period&-recordid=' . $_GET['-review'].'&--msg='.urlencode($msg));
		}
		elseif(isset($_GET['-review'])){
			$_SESSION['review']=$_GET['-review'];
		//	//echo $_SESSION['review'];
		}
	}

	function after_action_edit($params=array()){
		//If this is being edited coming from the review process, don't go back to "view" after editing, otherwise follow normal behavior.
		if(isset($_SESSION['review'])){
			$recordid = $_SESSION['review'];
			unset($_SESSION['review']);
		}
		else{
			$record =& $params['record'];
			header('Location: '.$record->getURL('-action=view').'&--msg='.urlencode('Record successfully added.'));
			exit;
		}
	}


//Use this to set the default filter for payroll period.
/*	function init(&$table){
		$app =& Dataface_Application::getInstance(); 
		$query =& $app->getQuery();

		echo "<pre>";print_r($query);echo "</pre>";

		//Check if "--init" is set in the URL. ("--" prefixes are unpreserved, meaning it will go away on filter change
		if(isset($query['--init'])){
			$query['payroll_period_id'] = 4;
			//unset($query['--init']);
		}

		echo "<pre>";print_r($query);echo "</pre>";
	}
*/

	function section__income_deductions(&$record){
		$app =& Dataface_Application::getInstance(); 
		$query =& $app->getQuery();
		$childString = '';		

		//Pull Payroll Configuration Information
		$payroll_config = df_get_record('_payroll_config', array('config_id'=>1));
		$employee_record = df_get_record('employees',array('employee_id'=>$record->val('employee_id')));

		
		// **** INCOME ****

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
			$addition_records = df_get_records_array('payroll_entries_income',array('payroll_entry_id'=>$record->val('payroll_entry_id')));
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
			$GLOBALS['taxable_income'] = $total_taxable_income;

			
		// **** DEDUCTIONS ****

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
			$addition_records = df_get_records_array('payroll_entries_deductions',array('payroll_entry_id'=>$record->val('payroll_entry_id')));
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
						//Exemption Amount - Where state is null = federal
						$exemption_record = df_get_record("_payroll_config_tax_exemptions",array("state"=>"FED"));
						$exemption_amount = $exemption_record->val($payroll_config->val('payroll_period')) * $employee_record->val('exemptions_federal');

						//Total taxable income minus exemptions
						$taxable_income_minus_exemptions = $total_taxable_income - $exemption_amount;

						//Calculate Income Tax
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
						$taxable_income_minus_exemptions = $total_taxable_income - $exemption_amount;

						//Calculate Income Tax
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


		//*** NET ***
		
		//This may end up in a $0.01 difference between the above totals due to rounding.
		$childString .= '<br><br>';
		$childString .= '<table class="view_add"><tr><td style="border=0"><b>Net Pay:</b> $' . number_format(($total_income) - ($total_deductions),2) . "</td></tr></table><br><br>";

		
		return array(
			'content' => "$childString",
			'class' => 'main',
			'label' => 'Income & Deductions',
			'order' => 10
		);
	}

	function section__contributions(&$record){
		$app =& Dataface_Application::getInstance(); 
		$query =& $app->getQuery();
		$childString = '';		
		
		// **** CONTRIBUTIONS ****
		
		//Start Table
		$childString .= '<b><u>Contributions</u></b><br><br>';
		$childString .= '<table class="view_add"><tr>
							<th>Description</th>
							<th>Amount Base</th>
							<th>Amount Percent</th>
							<th>Total</th>
							</tr>';
							
		//Get taxable income from the global scope variable
		$total_taxable_income = $GLOBALS['taxable_income'];
							
		//Parse through all Contribution Records for the Payroll Entry
		$addition_records = df_get_records_array('payroll_entries_contributions',array('payroll_entry_id'=>$record->val('payroll_entry_id')));
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
		$childSring .= "</table>";
		
		return array(
			'content' => "$childString",
			'class' => 'main',
			'label' => 'Contributions',
			'order' => 10
		);
	}

	
}

//Calculate the federal/state/city tax using the tax tables - City as yet to be implemented (Default = Federal)
function calculate_tax_table($taxable_income_minus_exemptions, $marital_status, $state = 'FED'){

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



?>