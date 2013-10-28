<?php

class tables_purchase_order_service {

	//Class Variables
	private $total_item_purchase = array(); //Create a class variable to store the values for modifying the inventory


	function getTitle(&$record){
		return "Service Purchase Order #" . $record->strval('purchase_id');
	}

	function purchase_id__display(&$record){
		return "S".$record->val('purchase_id');
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

			$purchaseorderRecords = $record->getRelatedRecords('purchase_order_service_items');
			$total_all_items = 0;
			
			foreach ($purchaseorderRecords as $purchaseorderRecord){
				//$inventory_record = df_get_record('inventory', array('inventory_id'=>$purchaseorderRecord['inventory_id']));
				$item_total = number_format($purchaseorderRecord['quantity'] * $purchaseorderRecord['purchase_price'],2);
				//$total_all_items += $purchaseorderRecord['quantity'] * $purchaseorderRecord['purchase_price'];
				$quantity = explode('.',$purchaseorderRecord['quantity']);
				if($quantity[1] != 0)
					$quantity[1] = '.'.$quantity[1];
				else
					$quantity[1] = '';

				
				$childString .= '<tr><td>' . $purchaseorderRecord['item_name'] .
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
			if($x['item_name'] == ''){
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

		//If the "Change Status To: Pending" button has been pressed.
		//Because both the $_GET and $query will be "" on a new record, check to insure that they are not empty.
		if(($_GET['-pending'] == $query['-recordid']) && ($query['-recordid'] != "")){
			$record->setValue('post_status',"Pending"); //Set status to Pending.
			$res = $record->save(null, true); //Save Record w/ permission check.

			//Check for errors.
			if ( PEAR::isError($res) ){
				// An error occurred
				//throw new Exception($res->getMessage());
				$msg = '<input type="hidden" name="--error" value="Unable to change status. This is most likely because you do not have the required permissions.">';
			}
			else
				$msg = '<input type="hidden" name="--msg" value="Status Changed to: Pending">';
			
			$childString .= '<form name="status_change">';
			$childString .= '<input type="hidden" name="-table" value="'.$query['-table'].'">';
			$childString .= '<input type="hidden" name="-action" value="'.$query['-action'].'">';
			$childString .= '<input type="hidden" name="-recordid" value="'.$record->getID().'">';

			$childString .= $msg;

			$childString .= '</form>';
			$childString .= '<script language="Javascript">document.status_change.submit();</script>';
		}
		elseif(	$record->val('post_status') == ''){
			$childString .= '<form>';
			$childString .= '<input type="hidden" name="-table" value="'.$query['-table'].'">';
			$childString .= '<input type="hidden" name="-action" value="'.$query['-action'].'">';
			$childString .= '<input type="hidden" name="-recordid" value="'.$record->getID().'">';
			
			$childString .= '<input type="hidden" name="-pending" value="'.$record->getID().'">';
			$childString .= '<input type="submit" value="Change Status to: Pending">';

			$childString .= '</form>';
		}
		//elseif(	$record->val('post_status') == 'Pending'){ //---can do this by linking to -action=ledger_post&selected="this_one"
		//	$childString .= 'Post';
		//}
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
		$record->setValue('purchase_order_id', "S".$record->val('purchase_id'));
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

	/*
		//$response =& Dataface_Application::getResponse();
		//$rlist = 'a';
		
		if($record->val('status') == '')
			$record->setValue('status','NCO');
		
		if($record->val('call_datetime') == '')
			$record->setValue('call_datetime',date('Y-m-d g:i a'));

		//*****************************************************************
		//********************Inventory Management Code********************
		//*****************************************************************

		//Get inventory modification values from the class variable cs_modify_inventory
		foreach($this->cs_modify_inventory as $iid=>$modify){
			$gen_inv = df_get_record('inventory', array('inventory_id'=>$iid));
			$gen_inv->setValue('quantity',($gen_inv->val('quantity') + $modify));
			//$gen_inv->save(null, true);
			$gen_inv->save();
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
	*/
	//}
	
	//function afterSave(&$record){
		//Calculate and Save the TOTAL
	
		//$itemRecords = $record->getRelatedRecords('purchase_order_inventory_items');
		
		//$total_item_purchase_price = 0;
		//foreach ($itemRecords as $itemRecord){
		//	$total_item_purchase_price += $itemRecord['purchase_price'] * $itemRecord['quantity'];
		//}
		
		//$total = $total_item_purchase_price + ( $total_item_purchase_price * $record->val('tax') ) + $record->val('shipping');
		
		//$record->setValue('total', $total);
		//$record->save(null, true);
		//$record->save();
	//}
	
	
	
	
	
}

?>
