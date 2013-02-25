<?php

class tables_call_slips {

	//private $inventory_values = array(); //Create a class variable to store the values from the grid field
	//private $cs_id = '';
	private $cs_modify_inventory = array(); //Create a class variable to store the values for modifying the inventory

	//Set the record title
	function getTitle(&$record){
		return "Call Slip # ".$record->val('call_id');
		//return $record->val('call_id');
	}

	function titleColumn(){
		return 'CONCAT("Call Slip # ",call_id)';
		//return 'CONCAT(call_id)';
	}


	/*function rel_call_slip_purchase_orders__permissions($record){
		return array(
			'add new related record' => 0,
			'add existing related record' => 0,
			'delete related record' => 0,
			'remove related record' => 0
		);
	}*/

	//function rel_call_slip_inventory__permissions($record){
	//	return array(
	//		'edit related records' => 0,
	//	);
	//}	


	//Pretty things up a bit.
		//function block__before_worktimes_widget() { echo '<div style="width: 700px">'; }
		//function block__after_worktimes_widget() { echo "</div>"; }

		//function block__before_purchase_order_widget() { echo '<div style="width: 600px">'; }
		//function block__after_purchase_order_widget() { echo "</div>"; }

		function block__before_inventory_widget() { echo '<div style="width: 600px">'; }
		function block__after_inventory_widget() { echo "</div>"; }	


	//This was silly and is now deprecated.
	//For the case where no contract is selected, save as value as '-1' instead of '0'
	//This alleviates issues with renaming via in the valuelist (0 is predefined)
	/*function contract_id__pushValue(&$record){
		$temp = $_POST['contract_id'];
		if($temp != 0)
			return $temp;
		return -1;

	//THIS IS BEING FLAKEY
		//	if($record->strval('contract_id') == 0)
		//		return -1;
	}
	*/	
	
	//DEFAULT VALUES
	function status__default(){
		return "OPEN";
	}
	
	function call_datetime__default() {
       return date('Y-m-d g:i a');
	}

	function call_id__default() {
       return "----<div class=\"formHelp\">A Call ID will be assigned after the first SAVE.</div>";		
	}

	
	
	//Add attitional details to the view tab - include employee work history
	function section__billing(&$record){

		$childString = "";

	
		//Hours Worked
			$childString .= '<b><u>Hours</u></b><br><br>';
			$childString .= '<table class="view_add"><tr>
								<th>Employee</th>
								<th>Arrive Time</th>
								<th>Depart Time</th>
								<th>Hours</th>
							</tr>';

			$employeeRecords = $record->getRelatedRecords('time_logs');
			foreach ($employeeRecords as $cs_er){
				//Pull the employee name out of the 'employees' table
				$rec = df_get_record('employees', array('employee_id'=>$cs_er['employee_id']));

				$arrive = Dataface_converters_date::datetime_to_string($cs_er['start_time']);
				$depart = Dataface_converters_date::datetime_to_string($cs_er['end_time']);
				//$hours = $cs_er['start_time']->diff($cs_er['end_time']);
				$hours = number_format(((strtotime($depart) - strtotime($arrive)) / 3600),1);
				$childString .= '<tr><td>' . $rec->display('first_name') . ' ' . $rec->display('last_name') .
								'</td><td>' . $arrive .
								"</td><td>" . $depart .
								"</td><td>" . $hours .
								"</td></tr>";
			}
			
			$childString .= '</table><br>';
			
		//Materials
			$childString .= '<b><u>Materials</u></b><br><br>';
			$childString .= '<table class="view_add"><tr>
								<th>PO# / Inventory</th>
								<th>Item</th>
								<th>Quantity</th>
								<th>List Cost</th>
								<th>Sale Cost</th>
								<th>(Markup)</th>
								<th>Total (List)</th>
								<th>Total (Sale)</th>
							</tr>';

			$total_materials_cost_list = 0;
			$total_materials_cost_sale = 0;
							
			$purchaseorderRecords = $record->getRelatedRecords('call_slip_purchase_orders');
			foreach ($purchaseorderRecords as $cs_pr){
				$subtotal_list = $cs_pr['cost_list'] * $cs_pr['quantity'];
				$subtotal_sale = $cs_pr['cost_sale'] * $cs_pr['quantity'];
				if($cs_pr['cost_list'] == 0)
					$markup = "";
				else
					$markup = number_format(100 * $cs_pr['cost_sale'] / $cs_pr['cost_list'] - 100) . '%';

				$childString .= '<tr><td style="text-align: right">PO #' . $cs_pr['purchase_id'] .
								'</td><td>' . $cs_pr['item_name'] .
								'</td><td style="text-align: right">' . $cs_pr['quantity'] .
								'</td><td style="text-align: right">' . $cs_pr['cost_list'] .
								'</td><td style="text-align: right">' . $cs_pr['cost_sale'] .
								'</td><td style="text-align: right">' . $markup . 
								'</td><td style="text-align: right">' . number_format($subtotal_list,2) .
								'</td><td style="text-align: right">' . number_format($subtotal_sale,2) .
								'</td></tr>';
				
				$total_materials_cost_list += $subtotal_list;
				$total_materials_cost_sale += $subtotal_sale;
			}
		
			$inventoryRecords = $record->getRelatedRecords('call_slip_inventory');
			foreach ($inventoryRecords as $cs_ir){
				//Pull the item name / cost out of the 'inventory' table
				$rec = df_get_record('inventory', array('inventory_id'=>$cs_ir['inventory_id']));

				$subtotal_list = $rec->display('purchase_cost') * $cs_ir['quantity'];
				$subtotal_sale = $rec->display('sell_cost') * $cs_ir['quantity'];
				if($rec->display('purchase_cost') == 0)
					$markup = "";
				else
					$markup = number_format(100 * $cs_ir['sell_cost'] / $cs_ir['purchase_cost'] - 100) . '%';

				$childString .= '<tr><td style="text-align: right">Inventory' .
								'</td><td>' . $rec->display('item_name') .
								'</td><td style="text-align: right">' . $cs_ir['quantity'] .
								'</td><td style="text-align: right">' . $cs_ir['purchase_cost'] .
								'</td><td style="text-align: right">' . $cs_ir['sell_cost'] . //$rec->display('sell_cost') . //--can do something here like if(inventory cost != this cost) different color
								'</td><td style="text-align: right">' . $markup .
								'</td><td style="text-align: right">' . number_format($subtotal_list,2) .
								'</td><td style="text-align: right">' . number_format($subtotal_sale,2) .
								'</td></tr>';

				$total_materials_cost_list += $subtotal_list;
				$total_materials_cost_sale += $subtotal_sale;
			}
			
			$childString .= '<tr><td></td><td></td><td></td><td></td><td></td><td></td>' .
							'<td style="text-align: right">' . number_format($total_materials_cost_list,2) . '</td>' .
							'<td style="text-align: right"><b>' . number_format($total_materials_cost_sale,2) . '</b></td>' .
							'</td></tr>';
			$childString .= '</table><br>';

		return array(
			'content' => "$childString",
			'class' => 'main',
			'label' => 'Billing Details',
			'order' => 10
		);
	}
	

	
	//CALENDAR MODULE FUNCTIONS
	function getBgColor($record){
		if ( $record->val('technician') == 1) return 'blue';
		if ( $record->val('technician') == 2) return 'blueviolet';
		if ( $record->val('technician') == 3) return 'brown';
		if ( $record->val('technician') == 4) return 'cadetblue';
		if ( $record->val('technician') == 5) return 'chocolate';
		if ( $record->val('technician') == 6) return 'cornflowerblue';
		if ( $record->val('technician') == 7) return 'crimson';
		if ( $record->val('technician') == 8) return 'darkcyan';
		if ( $record->val('technician') == 9) return 'darkred';
		if ( $record->val('technician') == 10) return 'green';
		if ( $record->val('technician') == 11) return 'goldenrod';
		else return 'rgb(0,0,0)';
	}
	
	function calendar__decorateEvent(Dataface_Record $record, &$event){
		$rec_site = df_get_record('sites', array('site_id'=>$record->val('site_id')));
		$rec_empl = df_get_record('employees', array('employee_id'=>$record->val('technician')));
		$event['title'] = "\nTech: " . $rec_empl->val('first_name') . " " . $rec_empl->val('last_name') . "\nSite: " . $rec_site->val('address');
	}

	//function init() {
	//	echo "foo";
	//	$app =& Dataface_Application::getInstance(); 
	//	$app->_conf['_modules'] = 'modules_calendar=modules/calendar/calendar.php';
	//}
	
	
	function inventory__validate(&$record, $value, &$params){
		//echo '<pre>';print_r($value); echo '</pre><br>';

		//Empty the error message
		$params['message'] = '';

		//Get the Call Slip ID
		$csid = $record->val('call_id');
		
		//Get rid of the last set in the array - it isn't needed for our use and causes issues
		unset($value['__loaded__']);

		//Determine what to add/subtract from the general inventory.
		//  First - Run through the data in the form list
		foreach ($value as $x){

			//Skip empty lines - do nothing (unless a quantity has been assigned, and then return an error)
			if($x['inventory_id'] == ''){
				if($x['quantity']){ //Case where the 'item_name' field has been left empty, but a quantity has been given
					$params['message'] .= 'CANNOT PROCESS INVENTORY: A quantity has been given, but an "Item Name" has not been assigned.';
					return false;
				}
			}

			//Process non-empty lines
			else{
				$params['message'] .= $x['inventory_id'] . ' -> ' . $x['quantity'] . '<br>';

				//Pull data from the "call_slip_inventory" and "inventory" tables.
				$cs_inv = df_get_record('call_slip_inventory', array('call_id'=>$csid, 'inventory_id'=>$x['inventory_id']));
				$gen_inv = df_get_record('inventory', array('inventory_id'=>$x['inventory_id']));

				//Create some variables for simplicity.
				$item_name = $gen_inv->display('item_name');
				$gen_inv_q = $gen_inv->display('quantity');
				$new_cs_i_q = $x['quantity'];

				
				//Check insure that each item in only entered in once.
				//We do this to simplify things significantly, and allow us to safely ignore some things that would otherwise have to be handled.
				foreach ($value as $y)
				{
					//Check if different lines have the same 'inventory_id'
					if( ($x['__order__'] != $y['__order__']) && ($x['inventory_id'] == $y['inventory_id'])){
						$params['message'] .= 'CANNOT PROCESS INVENTORY: The item: "'. $item_name .'" has been added multiple times. Please add each item only once.<br>';
						return 0;
					}
				}

				//If this is a new entry, $cur_cs_i_q = 0
				if(!$cs_inv)
					$cur_cs_i_q = 0;

				//Otherwise, pull from the 'call_slip_inventory' record
				else
					$cur_cs_i_q = $cs_inv->display('quantity');

				//Modify inventory variable.
				$mod_inv = $new_cs_i_q - $cur_cs_i_q;

				//Check if the quantity has changed. If so... do some things.
				if($new_cs_i_q != $cur_cs_i_q){
					//First we check to make sure that the quantity of the item being added is not greater than what's in the inventory.
					//If it is, cause an error and go no further.
					if($mod_inv > $gen_inv_q){
						$params['message'] .=	'CANNOT PROCESS INVENTORY: The current stock in inventory for "'. $item_name .'" is ' . $gen_inv_q . '.<br>' .
												'You are trying to add ' . $mod_inv . ' which exceeds the amount in inventory.<br>';
						return 0;
					}			

					//Now, save the inventory modification to the class variable cs_modify_inventory. The actual inventory will be modified/saved in the beforeSave() function.
					//We don't save here because 1) this function is actually run twice, and thus the inventory would be modified x2, and 2) other potential validation checks failing.
					$this->cs_modify_inventory[$x['inventory_id']] = (- $mod_inv);

					//*****Output for testing purposes
					//$params['message'] .= 'Value for "'. $item_name.'" has been modified. Changing from ' . $cur_cs_i_q . ' to ' . $new_cs_i_q . '.<br>';
					//$params['message'] .=	'Modify inventory by: ' . $mod_inv . '.<br>' .
					//						'Inventory was: ' . $gen_inv_q . ', Will now be: ' . ($gen_inv_q-$mod_inv) . '<br>';
					//*****
				}
			}
		}

		//  Now check to see if any lines have been removed
		$cs_inv = df_get_records_array('call_slip_inventory', array('call_id'=>$csid));
		foreach($cs_inv as $x)
		{
			//Clear the "found" key
			$found = 0;
			
			//Compare against the lines in $value to see if any are missing.
			foreach ($value as $y){
				//Check if the 'inventory_id' is in the list and set key
				if( $x->val('inventory_id') == $y['inventory_id'])
					$found = 1;
			}

			//If the item was not found, it has been removed, and we add the quantity back to inventory.
			if($found == 0){
				//Get matching inventory record
				$gen_inv = df_get_record('inventory', array('inventory_id'=>$x->val('inventory_id')));
				$gen_inv_q = $gen_inv->display('quantity');

				//Modify the inventory.
				$this->cs_modify_inventory[$x->val('inventory_id')] = $x->val('quantity');


				//*****Output for testing purposes
				//$params['message'] .=	'Item '. $gen_inv->val('item_name') .' Removed!<br>'.
				//						'Modify inventory by: ' . $x->val('quantity') . '.<br>' .
				//						'Inventory was: ' . $gen_inv_q . ', Will now be: ' . ($gen_inv_q+$x->val('quantity')) . '<br>';
				//*****

			}
		}
		
		//If no errors have occured, move along.
		//print_r($this->cs_modify_inventory);
		return 1;
	}


	function beforeSave(&$record){
		//$response =& Dataface_Application::getResponse();
		//$rlist = 'a';

		//*****************************************************************
		//********************Inventory Management Code********************
		//*****************************************************************

		//Get inventory modification values from the class variable cs_modify_inventory
		foreach($this->cs_modify_inventory as $iid=>$modify){
			$gen_inv = df_get_record('inventory', array('inventory_id'=>$iid));
			$gen_inv->setValue('quantity',($gen_inv->val('quantity') + $modify));
			$gen_inv->save(null, true);
			//return PEAR::raiseError('END',DATAFACE_E_NOTICE);
		}

		$inventoryRecords = $record->getRelatedRecords('call_slip_inventory');
		foreach ($inventoryRecords as $cs_ir){
			if ($cs_ir['sell_cost'] == ''){
				$inv_rec = df_get_record('inventory', array('inventory_id'=>$cs_ir['inventory_id']));
				$csi_rec = df_get_record('call_slip_inventory', array('csi_id'=>$cs_ir['csi_id']));
				$csi_rec->setValue('sell_cost', $inv_rec->val('sell_cost'));
				$csi_rec->setValue('purchase_cost', $inv_rec->val('purchase_cost'));
				$csi_rec->save();
			}
		}
		
		//*********************************************************************
		//********************END Inventory Management Code********************
		//*********************************************************************

		//if($rlist){
		//	$response['--msg'] = "Data: ".$rlist;
		//	return PEAR::raiseError("FIN",DATAFACE_E_NOTICE);
		//}
	}

}
?>