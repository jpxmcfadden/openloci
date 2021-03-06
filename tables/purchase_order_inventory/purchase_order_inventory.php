<?php

class tables_purchase_order_inventory {

	//Class Variables
	private $po_prefix = "I";
	private $total_item_purchase = array(); //Create a class variable to store the values for modifying the inventory

	//Permissions
		function getPermissions(&$record){
			//Check if the user is logged in & what their permissions for this table are.
			if( isUser() ){
				$userperms = get_userPerms('purchase_order_inventory');
				if($userperms == "view")
					return Dataface_PermissionsTool::getRolePermissions("READ ONLY"); //Assign Read Only Permissions
				elseif($userperms == "edit" || $userperms == "receive" || $userperms == "post"){
					if ( isset($record) ){
						if(	$record->val('post_status') == 'Posted' )
							return Dataface_PermissionsTool::getRolePermissions('NO_EDIT_DELETE');
					}
					return Dataface_PermissionsTool::getRolePermissions(myRole()); //Assign Permissions based on user Role (typically USER)
				}
			}

			//Default: No Access
			return Dataface_PermissionsTool::NO_ACCESS();
		}

		function __field__permissions($record){
			if ( isset($record) && ($record->val('post_status') == 'Posted' || $record->val('post_status') == 'Received') )
				return array('edit'=>0, 'delete'=>0);
		}
		
		//Remove the "edit" tab, if applicable. --- Field permissions are set to 'edit'=>0 anyway, but since changing "status" required general edit access via getPermissions(), which then automatically shows the tab - this needs to be visually disabled.
		function init(){
			$app =& Dataface_Application::getInstance();
			$query =& $app->getQuery();
			$record =& $app->getRecord();
			
			//Only on the 'view' page. Otherwise, causes issues with looking at the entire table (i.e. user sees a blank page).
			//If record exists & the status is set such that the record shouldn't be editable.
			if($query['-action'] == 'view' && ( isset($record) && ($record->val('post_status') == 'Posted' || $record->val('post_status') == 'Received') ))
				echo "<style>#record-tabs-edit{display: none;}</style>";
		}

		function post_status__permissions(&$record){
			//Check permissions & if allowed, set edit permissions for "account_status"
			if(get_userPerms('purchase_order_inventory') == "receive" || get_userPerms('purchase_order_inventory') == "post")
				return array("edit"=>1);
		}

		function received_date__permissions(&$record){
			//Check permissions & if allowed, set edit permissions for "account_status"
			if(get_userPerms('purchase_order_inventory') == "receive" || get_userPerms('purchase_order_inventory') == "post")
				return array("edit"=>1);
		}	



	function getTitle(&$record){
		return "Inventory Purchase Order #" . $record->strval('purchase_id');
	}

	function purchase_id__display(&$record){
		return $this->po_prefix.$record->val('purchase_id');
	}

	function purchase_date__default(){
		return date('Y-m-d');
	}
	
	//Add attitional details to the view tab
	function section__pricing(&$record){

		$childString = "";

			//Materials
			$childString .= '<b><u>Item List</u></b><br><br>';
			$childString .= '<table class="view_add"><tr><th>Item</th><th>Quantity</th><th>Purchase Price</th><th>Total (per item)</th></tr>';

			$purchaseorderRecords = $record->getRelatedRecords('purchase_order_inventory_items');
			$total_all_items = 0;
			
			foreach ($purchaseorderRecords as $purchaseorderRecord){
				$inventory_record = df_get_record('inventory', array('inventory_id'=>$purchaseorderRecord['inventory_id']));
				$item_total = number_format($purchaseorderRecord['quantity'] * $purchaseorderRecord['purchase_price'],2);
				//$total_all_items += $purchaseorderRecord['quantity'] * $purchaseorderRecord['purchase_price'];
				$quantity = explode('.',$purchaseorderRecord['quantity']);
				if($quantity[1] != 0)
					$quantity[1] = '.'.$quantity[1];
				else
					$quantity[1] = '';

				
				$childString .= '<tr><td>' . $inventory_record->val('item_name') .
								'</td><td style="text-align: right"><table style="width: 100%; border-collapse:collapse;"><tr>' .
																						'<td style="border: 0px solid black; padding: 0; text-align: right; width: 100%;">' . $quantity[0] .
																						'</td><td style="border: 0px solid black; padding: 0; text-align: left; width: 10px;">'.$quantity[1].'</td></tr></table>' .
								'</td><td style="text-align: right">$' . $purchaseorderRecord['purchase_price'] .
								'</td><td style="text-align: right">$' . $item_total .
								'</td></tr>';
			}

			$childString .= '<tr>
								<td style="border: 0px;"></td>
								<td style="border: 0px;"></td>
								<td style="border: 0px;"></td>
								<td style="text-align: right;"><b>$'.$record->val('item_total').'</b></td>
							</tr>';
							
			$childString .= '</table><br>';

			$childString .= '<table class="record-view-table">';
			$childString .= '<tr><th class="record-view-label">Tax</th><td>$'.number_format($record->val('item_total')*$record->val('tax'),2) . ' (' . $record->val('tax')*100 . '%)</td></tr>';
			$childString .= '<tr><th class="record-view-label">Shipping</th><td>'.$record->val('shipping').'</td></tr>';
			$childString .= '<tr><th class="record-view-label">PO Total</th><td>'.$record->val('total').'</td></tr>';
			$childString .= '</table>';
			
			
		return array(
			'content' => "$childString",
			'class' => 'main',
			//'class' => 'left',
			'label' => 'Pricing',
			'order' => 2
		);
	}

	function purchase_order_items__validate(&$record, $value, &$params){
		//Empty the error message
		$params['message'] = '';
		
		//Set ERROR message
		$msg = "ERROR PROCESSING PURCHASE ORDER: ";

		//Get the Purchase Order ID
		$po_id = $record->val('purchase_id');
		
		//Get rid of the last set in the array - it isn't needed for our use and causes issues
		unset($value['__loaded__']);

		//Set the item total to 0. We are going to add up and save values here. This probably *shouild* happen in afterSave(), but having problems with that at the moment.
		$item_total = 0.0;
		
		//Determine what to add/subtract from the general inventory.
		//  First - Run through the data in the form list
		foreach ($value as $x){

			//Skip empty lines - do nothing (unless a quantity has been assigned, and then return an error)
			if($x['inventory_id'] == ''){
				if($x['quantity']){ //Case where the 'item_name' field has been left empty, but a quantity has been given
					$params['message'] .= $msg.'A quantity has been given, but an "Item" has not been assigned.';
					return false;
				}
			}
			
			if($x['quantity'] < 0){
				$params['message'] .= $msg.'A negative quantity has been assigned.';
				return false;
			}

			$item_total += $x['purchase_price'] * $x['quantity'];
			$params['message'] .= $x['purchase_price'] * $x['quantity'] .'->'. $item_total . '<br>';
		}
			
		//Set the "subtotal"
		$this->total_item_purchase = $item_total;
	//	$record->setValue('item_total', $item_total);
	//	$record->save();

	//$params['message'] .= $item_total;
	//print_r($record->vals());
	//return 0;
		//If no errors have occured, move along.
		return 1;
	}
	

	function section__status(&$record){
		$app =& Dataface_Application::getInstance(); 
		$query =& $app->getQuery();
		$childString = '';

		//Check User Permissions
		$userperms = get_userPerms('purchase_order_inventory');
		if($userperms == "receive" || $userperms == "post"){
			//Get current record status - in case there is an error, so it's easy to revert back.
			$initial_record_status = $record->val('post_status');
			$initial_received_date = $record->val('received_date');
			
			//Pull the items in the PO
			$item_records = $record->getRelatedRecords("purchase_order_inventory_items");
		
			//If the "Change Status To: Received" button has been pressed.
			//Because both the $_GET and $query will be "" on a new record, check to insure that they are not empty.
			if(($_GET['-received'] == $query['-recordid']) && ($query['-recordid'] != "")){

				$record->setValue('post_status',"Received"); //Set status to Received.
				if($record->val('received_date') == null)
					$record->setValue('received_date',date("Y-m-d")); //Set received date, if not already entered.
				$res = $record->save(null, true); //Save Record w/ permission check.

				//Check for errors.
				if ( PEAR::isError($res) ){ // Set error message
					$msg = '<input type="hidden" name="--error" value="Unable to change status. This is most likely because you do not have the required permissions.">';
				}
				else{ //Process the PO items
					$res_error = 0;
					//$res_saved[]; //To keep track of which records have been updated in case of an error
					//Process all the Items in the Purchase Order
					foreach($item_records as $j=>$item_record){
						//Pull inventory record, Calculate & Set new inventory 'quantity'
							$inventory_table = 'inventory';
							$inventory_id = 'inventory_id';
							$inventory_record = df_get_record($inventory_table, array($inventory_id=>$item_record[$inventory_id]));
							$current_inventory_quantity = $inventory_record->val('quantity');
							$new_inventory_quantity = $current_inventory_quantity + $item_record['quantity'];
							$inventory_record->setValue('quantity', $new_inventory_quantity);
																		
						//Save Records
							$res = $inventory_record->save(null, true);			//calculates it's Average Purchase Price based on purchase history

						//CHECK FOR ERRORS
							if ( PEAR::isError($res) ){
								$res_error = 1;
								break;
							}
							else
								$res_saved[$item_record[$inventory_id]] = $item_record["quantity"];
					}
					
					//If there was an error
					if($res_error != 0){
						//Revert Status / Received Date
						$record->setValue('post_status',$initial_record_status); //Set status back.
						$record->setValue('received_date',$initial_received_date); //Set received date back.
						$res = $record->save(null, true); //Save Record w/ permission check.
						
						//Create Error Message
						$msg = '<input type="hidden" name="--error" value="Unable to change status due to a problem updating the tool inventory record. This is most likely because you do not have the required permissions.">';

						//Auto revert any saved entries
						//if(!isempty($res_saved)) //It is unlikely that if one item record didn't save that any others did, but just in case, there needs to be a condition for it. For right now, just print an error directing to user to manually fix the problem - eventually should automatically remove any entries that were added.
						//	$msg = '<input type="hidden" name="--error" value="There following inventory items were modified and will need to be set back manually:"' .
						//		foreach($res_saved as $id=>$quantity){
						//			. " ID (" . $id . "), Quantity (" . $quantity . ")";
						//		}
						//	. '>';
					}
					else
						$msg = '<input type="hidden" name="--msg" value="Status Changed to: Received">';
				}	
				
				$childString .= '<form name="status_change">';
				$childString .= '<input type="hidden" name="-table" value="'.$query['-table'].'">';
				$childString .= '<input type="hidden" name="-action" value="'.$query['-action'].'">';
				$childString .= '<input type="hidden" name="-recordid" value="'.$record->getID().'">';

				$childString .= $msg;

				$childString .= '</form>';
				$childString .= '<script language="Javascript">document.status_change.submit();</script>';
			}
			elseif(($_GET['-unreceive'] == $query['-recordid']) && ($query['-recordid'] != "")){
			
				$record->setValue('post_status',""); //Set status to null.
				$res = $record->save(null, true); //Save Record w/ permission check.

				//Check for errors.
				if ( PEAR::isError($res) ){
					$msg = '<input type="hidden" name="--error" value="Unable to change status. This is most likely because you do not have the required permissions.">';
				}
				else{ //Process the PO items
					$res_error = 0;
					//$res_saved[]; //To keep track of which records have been updated in case of an error
					//Process all the Items in the Purchase Order
					foreach($item_records as $j=>$item_record){
						//Pull inventory record, Calculate & Set new inventory 'quantity'
							$inventory_table = 'inventory';
							$inventory_id = 'inventory_id';
							$inventory_record = df_get_record($inventory_table, array($inventory_id=>$item_record[$inventory_id]));
							$current_inventory_quantity = $inventory_record->val('quantity');
							$new_inventory_quantity = $current_inventory_quantity - $item_record['quantity'];
							$inventory_record->setValue('quantity', $new_inventory_quantity);
																		
						//Save Records
							$res = $inventory_record->save(null, true);			//calculates it's Average Purchase Price based on purchase history

						//CHECK FOR ERRORS
							if ( PEAR::isError($res) ){
								$res_error = 1;
								break;
							}
							else
								$res_saved[$item_record[$inventory_id]] = $item_record["quantity"];
					}
					
					//If there was an error
					if($res_error != 0){
						//Revert Status
						$record->setValue('post_status',$initial_record_status); //Set status back.
						$res = $record->save(null, true); //Save Record w/ permission check.
						
						//Create Error Message
						$msg = '<input type="hidden" name="--error" value="Unable to change status due to a problem updating the tool inventory record. This is most likely because you do not have the required permissions.">';

						//Auto revert any saved entries
						//if(!isempty($res_saved)) //It is unlikely that if one item record didn't save that any others did, but just in case, there needs to be a condition for it. For right now, just print an error directing to user to manually fix the problem - eventually should automatically remove any entries that were added.
						//	$msg = '<input type="hidden" name="--error" value="There following inventory items were modified and will need to be set back manually:"' .
						//		foreach($res_saved as $id=>$quantity){
						//			. " ID (" . $id . "), Quantity (" . $quantity . ")";
						//		}
						//	. '>';
					}
					else
						$msg = '<input type="hidden" name="--msg" value="PO has been Un-Received">';
				}
					
				
				$childString .= '<form name="status_change">';
				$childString .= '<input type="hidden" name="-table" value="'.$query['-table'].'">';
				$childString .= '<input type="hidden" name="-action" value="'.$query['-action'].'">';
				$childString .= '<input type="hidden" name="-recordid" value="'.$record->getID().'">';

				$childString .= $msg;

				$childString .= '</form>';
				$childString .= '<script language="Javascript">document.status_change.submit();</script>';
			}
			elseif($record->val('post_status') == ''){
				$childString .= '<form>';
				$childString .= '<input type="hidden" name="-table" value="'.$query['-table'].'">';
				$childString .= '<input type="hidden" name="-action" value="'.$query['-action'].'">';
				$childString .= '<input type="hidden" name="-recordid" value="'.$record->getID().'">';
				
				$childString .= '<input type="hidden" name="-received" value="'.$record->getID().'">';
				$childString .= '<input type="submit" value="Change Status to: Received">';

				$childString .= '</form>';
			}
			elseif($record->val('post_status') == 'Received'){
				$childString .= '<form>';
				$childString .= '<input type="hidden" name="-table" value="'.$query['-table'].'">';
				$childString .= '<input type="hidden" name="-action" value="'.$query['-action'].'">';
				$childString .= '<input type="hidden" name="-recordid" value="'.$record->getID().'">';
				
				$childString .= '<input type="hidden" name="-unreceive" value="'.$record->getID().'">';
				$childString .= '<input type="submit" value="Change Status to: Un-Receive">';

				$childString .= '</form>';
			}
			else {
				$childString .= "No further options available";
			}
		}
		else {
			$childString .= "No further options available";
		}		
		
		//if(	$record->val('post_status') == '')
		return array(
			'content' => "$childString",
			'class' => 'main',
			'label' => 'Change Status',
			'order' => 10
		);
	}
	
	
	

	function beforeSave(&$record){
		if($this->total_item_purchase){
			//Calculate and Save the TOTAL
			$total_item_purchase_price = $this->total_item_purchase;
			$total = $total_item_purchase_price + ( $total_item_purchase_price * $record->val('tax') ) + $record->val('shipping');

			$record->setValue('item_total', $total_item_purchase_price);
			$record->setValue('total', $total);
			
		}
		//If "shipping" if left blank, set to 0.00
		if($record->val('shipping') == null)
			$record->setValue('shipping', 0);
	}

	function afterInsert(&$record){
		//PO Full ID: prefix+purchase_id
		$record->setValue('purchase_order_id', $this->po_prefix.$record->val('purchase_id'));
		$record->save();
	}	
	
	
	/*
		//Create purchase history records for all items. -- Goes in Action.
		//Get items list
		$po_itemRecords = df_get_records_array('purchase_order_inventory_items', array('purchase_id'=>$po_id));

		
		$purchase_history_record = new Dataface_Record('inventory_purchase_history', array());
		$purchase_history_record->setValues(
										array(
											'inventory_id'=>,
											'purchase_order_id'=>,
											'purchase_date'=>,
											'vendor'=>,
											'purchase_price'=>
										)
									);
		$purchase_history_record->save(null, true);
	*/
	
}

?>
