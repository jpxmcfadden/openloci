<?php

class tables_accounts_payable {

//SQL to create VIEW: (SELECT purchase_order_id, assigned_voucher_id, vendor_id FROM `purchase_order_inventory` WHERE assigned_voucher_id IS NULL) UNION (SELECT purchase_order_id, assigned_voucher_id , vendor_id FROM `purchase_order_service` WHERE assigned_voucher_id IS NULL)

	//Permissions
	function getPermissions(&$record){
		//Check if the user is logged in & what their permissions for this table are.
		if( isUser() ){
			$userperms = get_userPerms('accounts_payable');
			if($userperms == "view")
				return Dataface_PermissionsTool::getRolePermissions("READ ONLY"); //Assign Read Only Permissions
			elseif($userperms == "edit" || $userperms == "post"){
				//Check status, determine if record should be uneditable.
				if ( isset($record) && $record->val('post_status') == 'Posted')
						return Dataface_PermissionsTool::getRolePermissions('NO_EDIT_DELETE');
				return Dataface_PermissionsTool::getRolePermissions(myRole()); //Assign Permissions based on user Role (typically USER)
			}
		}

		//Default: No Access
		return Dataface_PermissionsTool::NO_ACCESS();
	}


	
	function getTitle(&$record){
		$po_type = substr($record->val('purchase_order_id'),0,1);
		return 'Voucher ID #' . $record->val('voucher_id') . " (" . $record->strval('voucher_date') . ") - Type: " . $po_type . ", Invoice ID: " . $record->val('invoice_id');

		//return 'Invoice ID: ' . $record->val('invoice_id') . ' - Status: ' . $record->val('post_status');
	}

	function titleColumn(){
		return 'CONCAT("Invoice ID: ", invoice_id," - Status: ",post_status)';
	}	

	function voucher_date__default(){
		return date('Y-m-d');
	}

	function amount__display(&$record){
		if($record->val('amount') != NULL)
			return "$" . $record->val('amount');
			
	}

	function customer_id__display(&$record){
		if($record->val('customer_id') == NULL)
			return "---";
			
		$customer_record = df_get_record('customers', array('customer_id'=>$record->val('customer_id')));
		return $customer_record->val('customer');
	}

	function site_id__display(&$record){
		if($record->val('site_id') == NULL)
			return "---";
			
		$site_record = df_get_record('customer_sites', array('site_id'=>$record->val('site_id')));
		return $site_record->val('site_address');
	}

	function section__amount(&$record){
		//$app =& Dataface_Application::getInstance(); 
		//$query =& $app->getQuery();
		$childString = '';

		//Get selected PO Type
		$po_type = $record->val('type');

		//Open record from appropriate table
		if($po_type == 'S'){
			$po_record = df_get_record('purchase_order_service', array('purchase_order_id'=>$record->val('purchase_order_id')));
		}
		elseif($po_type == 'I'){
			$po_record = df_get_record('purchase_order_inventory', array('purchase_order_id'=>$record->val('purchase_order_id')));
		}
		elseif($po_type == 'O'){
			$po_record = df_get_record('purchase_order_office', array('purchase_order_id'=>$record->val('purchase_order_id')));
		}
		elseif($po_type == 'R'){
			$po_record = df_get_record('purchase_order_services_rendered', array('purchase_order_id'=>$record->val('purchase_order_id')));
		}
		else
			return array('content' => '<table><tr><td style="background-color: #ffa07b;">ERROR: Could not read PO</td></tr></table>','class' => 'main','label' => 'Amount','order' => 9);		
			
		$item_total = $po_record->val('item_total');
		$shipping = $po_record->val('shipping');
		$tax = round($po_record->val('tax') * $po_record->val('item_total'),2);
		if($record->val('apply_discount') == 1){
			if($record->val("modify_discount") != null)
				$discount = $record->val('modify_discount');
			else{
				$vendor_record = df_get_record("vendors", array('vendor_id'=>$record->val('vendor_id')));
				$discount = round($vendor_record->val('discount_percent') * $item_total / 100,2);
			}
		}

		$total_amount = $item_total + $shipping + $tax - $discount;
		
		$childString .= "<table>
							<tr><td>Item Total from PO</td><td align=right>$" . number_format($item_total,2) . "</td></tr>";
		if($discount != null) $childString .= "<tr><td>Discount</td><td align=right>-$" . number_format($discount,2) . "</td></tr>";
		$childString .= "	<tr><td>Tax</td><td align=right>$" . number_format($tax,2) . "</td></tr>
							<tr><td>Shipping</td><td align=right>$" . number_format($shipping,2) . "</td></tr>
							<tr><td><b>TOTAL</b></td><td align=right><b>$" . number_format($total_amount,2) . "</b></td></tr>
						</table>";
		
		
		return array(
			'content' => "$childString",
			'class' => 'main',
			'label' => 'Amount',
			'order' => 9
		);
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
		//Show Pending button
		elseif(	$record->val('post_status') == ''){
			$childString .= '<form>';
			$childString .= '<input type="hidden" name="-table" value="'.$query['-table'].'">';
			$childString .= '<input type="hidden" name="-action" value="'.$query['-action'].'">';
			$childString .= '<input type="hidden" name="-recordid" value="'.$record->getID().'">';
			
			$childString .= '<input type="hidden" name="-pending" value="'.$record->getID().'">';
			$childString .= '<input type="submit" value="Change Status to: Pending">';

			$childString .= '</form>';
		}
		//If the "Create Credit Voucher" button has been pressed.
		elseif(($_GET['-credit'] == $query['-recordid']) && ($query['-recordid'] != "")){
			//Create the Credit Voucher Record
			$new_record = new Dataface_Record('accounts_payable', array());
			$new_record->setValue('voucher_date',date('Y-m-d'));
			$new_record->setValue('invoice_id',$record->val('invoice_id'));
			$new_record->setValue('invoice_date',$record->val('invoice_date'));
			$new_record->setValue('post_status','Pending');
			$new_record->setValue('purchase_order_id',$record->val('purchase_order_id'));
			$new_record->setValue('vendor_id',$record->val('vendor_id'));
			$new_record->setValue('customer_id',$record->val('customer_id'));
			$new_record->setValue('site_id',$record->val('site_id'));
			$new_record->setValue('type',$record->val('type'));
			$new_record->setValue('credit',"Credit for Voucher " . $record->val('voucher_id'));
			$new_record->setValue('description',"Credit to reverse entry for Voucher ID ".$record->val('voucher_id'));
			$new_record->setValue('amount',$record->val('amount'));
			$new_record->setValue('apply_discount',$record->val('apply_discount'));
			$new_record->setValue('modify_discount',$record->val('modify_discount'));
			$new_record->setValue('account_credit',$record->val('account_debit'));
			$new_record->setValue('account_debit',$record->val('account_credit'));
			$res_n = $new_record->save(null, true); //Save Record w/ permission check.

			//Set the check to VOID & Credit to Credit Voucher ID
			if($record->val("check_number") == "")
				$record->setValue('check_number',"VOID");
			else
				$record->setValue('check_number',"VOID - ".$record->val('check_number'));
			$record->setValue('credit',"Credited (".$new_record->val('voucher_id').")");
			$res_r = $record->save(); //Save Record w/ permission check.

			//Unset the assigned Voucher ID in the Purchase Order - so that it can be redone with a new voucher.
				$po_type = $record->val('type');

				//Open record from appropriate table
				if($po_type == 'S'){
					$po_record = df_get_record('purchase_order_service', array('purchase_order_id'=>$record->val('purchase_order_id')));
				}
				elseif($po_type == 'I'){
					$po_record = df_get_record('purchase_order_inventory', array('purchase_order_id'=>$record->val('purchase_order_id')));
				}
				elseif($po_type == 'O'){
					$po_record = df_get_record('purchase_order_office', array('purchase_order_id'=>$record->val('purchase_order_id')));
				}
				elseif($po_type == 'R'){
					$po_record = df_get_record('purchase_order_services_rendered', array('purchase_order_id'=>$record->val('purchase_order_id')));
				}
				else
					return PEAR::raiseError("something went wrong..." . $po_type,DATAFACE_E_NOTICE);
						
				//Set to null
				$po_record->setValue('assigned_voucher_id',NULL);
				$res_p = $po_record->save();


			//Check for errors.
			$errors = 0;
			$msg = "";
			if ( PEAR::isError($res_n)){ //Create new
				$msg .= '<input type="hidden" name="--error" value="Unable to create a Credit Voucher. This is most likely because you do not have the required permissions.<br>">';
				$errors = 1;
			}
			if ( PEAR::isError($res_r)){ //Void check
				$msg .= '<input type="hidden" name="--error" value="Unable to VOID Check. Please see your system administrator to correct this issue.<br>">';
				$errors = 1;
			}
			if ( PEAR::isError($res_p)){ //Unset PO
				$msg .= '<input type="hidden" name="--error" value="Unable to clear the Purchase Order record. The associated Purchase order will not be able to be assigned again until this is fixed. Please see your system administrator to correct this issue.">';
				$errors = 1;
			}
			if($errors == 0)
				$msg = '<input type="hidden" name="--msg" value="Credit Voucher Created.">';
			
			$childString .= '<form name="status_change">';
			$childString .= '<input type="hidden" name="-table" value="'.$query['-table'].'">';
			$childString .= '<input type="hidden" name="-action" value="'.$query['-action'].'">';
			$childString .= '<input type="hidden" name="-recordid" value="'.$record->getID().'">';

			$childString .= $msg;

			$childString .= '</form>';
			$childString .= '<script language="Javascript">document.status_change.submit();</script>';
		}

		//Show Credit Voucher button
		elseif(	$record->val('post_status') == 'Posted' && $record->val('credit') == ""){
			$childString .= '<form>';
			$childString .= '<input type="hidden" name="-table" value="'.$query['-table'].'">';
			$childString .= '<input type="hidden" name="-action" value="'.$query['-action'].'">';
			$childString .= '<input type="hidden" name="-recordid" value="'.$record->getID().'">';
			
			$childString .= '<input type="hidden" name="-credit" value="'.$record->getID().'">';
			$childString .= '<input type="submit" value="Create Credit Voucher">';

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
		//Check if the PO field has been changed... if so, we need to null the po's assigned_voucher_id field
		if($record->valueChanged('purchase_order_id') && $record->val('purchase_order_id') != NULL){
			//Pull the purchase order type from the previously saved (snapshot) purchase_order_id field (first character)
			$po_old = $record->getSnapshot(['purchase_order_id']);
			$po_type = substr($po_old['purchase_order_id'],0,1);

			//If this is a new record, don't do this (ie po_type will be null) -- good programming: po_type above returns warning, check po_old instead and assign po_type inside
			if($po_type != null){
				//Open record from appropriate table
				if($po_type == 'S'){
					$po_record = df_get_record('purchase_order_service', array('purchase_order_id'=>$po_old['purchase_order_id']));
				}
				elseif($po_type == 'I'){
					$po_record = df_get_record('purchase_order_inventory', array('purchase_order_id'=>$po_old['purchase_order_id']));
				}
				elseif($po_type == 'O'){
					$po_record = df_get_record('purchase_order_office', array('purchase_order_id'=>$po_old['purchase_order_id']));
				}
				elseif($po_type == 'R'){
					$po_record = df_get_record('purchase_order_services_rendered', array('purchase_order_id'=>$po_old['purchase_order_id']));
				}
				else
					return PEAR::raiseError("something went wrong..." . $po_type,DATAFACE_E_NOTICE);
					
				$po_record->setValue('assigned_voucher_id',NULL);
				$po_record->save();
			}
		}
		
		//Assign Vendor & Type (& for SPO, Customer / Site) from PO
		//(no if isset(vendor) is potentially redundant, but easy & always assigns correct vendor in case of user changing the vendor after the PO is selected -- can add some error checking / notification functions here)
			//Get the currently selected purchase order record
			$po_type = substr($record->val('purchase_order_id'),0,1);
			$record->setValue('type',$po_type);
			
			if($po_type == 'S')
				$po_record = df_get_record('purchase_order_service', array('purchase_order_id'=>$record->val('purchase_order_id')));
			elseif($po_type == 'I')
				$po_record = df_get_record('purchase_order_inventory', array('purchase_order_id'=>$record->val('purchase_order_id')));
			elseif($po_type == 'O')
				$po_record = df_get_record('purchase_order_office', array('purchase_order_id'=>$record->val('purchase_order_id')));
			elseif($po_type == 'R')
				$po_record = df_get_record('purchase_order_services_rendered', array('purchase_order_id'=>$record->val('purchase_order_id')));
			else
				return PEAR::raiseError("something went wrong..." . $po_type,DATAFACE_E_NOTICE);
			
		//Assign Vendor based on PO (overwrite vendor, if already selected we don't care, and if someone changes the vendor after selecting a PO - but not the PO, this will reset to match)
		$record->setValue('vendor_id',$po_record->val('vendor_id'));
			
		//If this is a Service PO, add customer & site
		if($po_type == 'S'){
			$cs_record = df_get_record('call_slips', array('call_id'=>$po_record->val('callslip_id')));
			$record->setValue('customer_id',$cs_record->val('customer_id'));
			$record->setValue('site_id',$cs_record->val('site_id'));
		}
			
		//If Credit Account is left blank, assign default AP account.
		if($record->val('account_credit') == null){
			$account_record = df_get_record('_account_defaults', array('default_id'=>('1')));
			$record->setValue('account_credit', $account_record->val('accounts_payable'));
		}

		//If Debit Account is left blank, assign default account from vendor.
		if($record->val('account_debit') == null){
			$account_record = df_get_record('vendors', array('vendor_id'=>$po_record->val('vendor_id')));
			$record->setValue('account_debit', $account_record->val('default_account'));
		}
		
		//Calculate Total & assign to the Amount field
		$item_total = $po_record->val('item_total');
		$shipping = $po_record->val('shipping');
		$tax = round($po_record->val('tax') * $po_record->val('item_total'),2);
		if($record->val('apply_discount') == 1){
			if($record->val("modify_discount") != null)
				$discount = $record->val('modify_discount');
			else{
				$vendor_record = df_get_record("vendors", array('vendor_id'=>$record->val('vendor_id')));
				$discount = round($vendor_record->val('discount_percent') * $item_total / 100,2);
			}
		}
		$total_amount = $item_total + $shipping + $tax - $discount;		
		$record->setValue('amount', $total_amount);
		
		
		
	}



	function afterSave(&$record){
		//After saving the record, assign the selected po's assigned_voucher_id field to the record id
		if($record->val('purchase_order_id') != NULL && $record->val('credit') == ""){
			//Pull the purchase order type
			$po_type = $record->val('type');
			
			//Open record from appropriate table
			if($po_type == 'S'){
				$po_record = df_get_record('purchase_order_service', array('purchase_order_id'=>$record->val('purchase_order_id')));
			}
			elseif($po_type == 'I'){
				$po_record = df_get_record('purchase_order_inventory', array('purchase_order_id'=>$record->val('purchase_order_id')));
			}
			elseif($po_type == 'O'){
				$po_record = df_get_record('purchase_order_office', array('purchase_order_id'=>$record->val('purchase_order_id')));
			}
			elseif($po_type == 'R'){
				$po_record = df_get_record('purchase_order_services_rendered', array('purchase_order_id'=>$record->val('purchase_order_id')));
			}
			else
				return PEAR::raiseError("something went wrong..." . $po_type,DATAFACE_E_NOTICE);
				
			//Assign and Save the record id to the assigned_voucher_id field.
			$po_record->setValue('assigned_voucher_id',$record->val('voucher_id'));
			$po_record->save();
			
		//	$po_rec = df_get_record('accounts_payable_unassigned_purchase_orders', array('purchase_order_id_full'=>$record->val('purchase_order_id')));
		//	$po_record->setValue('assigned_voucher_id',$record->val('voucher_id'));
		//	$po_rec->save();
		//	return PEAR::raiseError($foo,DATAFACE_E_NOTICE);
		}
	}
	
}
?>
