<?php

class tables_accounts_payable {

//SQL to create VIEW: (SELECT purchase_order_id, assigned_voucher_id, vendor_id FROM `purchase_order_inventory` WHERE assigned_voucher_id IS NULL) UNION (SELECT purchase_order_id, assigned_voucher_id , vendor_id FROM `purchase_order_service` WHERE assigned_voucher_id IS NULL)

	//Permissions
	function getPermissions(&$record){
		//First check if the user is logged in.
		if( isUser() ){
			//Check status, determine if record should be uneditable.
			if ( isset($record) ){
				if(	$record->val('post_status') == 'Posted')
					return Dataface_PermissionsTool::getRolePermissions('NO_EDIT_DELETE');
			}
		}
		else
			return Dataface_PermissionsTool::NO_ACCESS();
	}


	function getTitle(&$record){
		return 'Invoice ID: ' . $record->val('invoice_id') . ' - Status: ' . $record->val('post_status');
	}

	function titleColumn(){
		return 'CONCAT("Invoice ID: ", invoice_id," - Status: ",post_status)';
	}	

	function voucher_date__default(){
		return date('Y-m-d');
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
		//Check if the PO field has been changed... if so, we need to null the po's assigned_voucher_id field
		if($record->valueChanged('purchase_order_id') && $record->val('purchase_order_id') != NULL){
			//Pull the purchase order type from the previously saved (snapshot) purchase_order_id field (first character)
			$po_old = $record->getSnapshot(['purchase_order_id']);
			$po_type = substr($po_old['purchase_order_id'],0,1);
				
			//Open record from appropriate table
			if($po_type == 'S'){
				$po_record = df_get_record('purchase_order_service', array('purchase_order_id'=>$po_old['purchase_order_id']));
			}
			elseif($po_type == 'I'){
				$po_record = df_get_record('purchase_order_inventory', array('purchase_order_id'=>$po_old['purchase_order_id']));
			}
			else
				return PEAR::raiseError("something went wrong..." . $po_type,DATAFACE_E_NOTICE);
				
			$po_record->setValue('assigned_voucher_id',NULL);
			$po_record->save();	
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
			else
				return PEAR::raiseError("something went wrong..." . $po_type,DATAFACE_E_NOTICE);
			
			//Assign & Save data
			$record->setValue('vendor_id',$po_record->val('vendor_id'));
			if($po_type == 'S'){
				$cs_record = df_get_record('call_slips', array('call_id'=>$po_record->val('callslip_id')));
				$record->setValue('customer_id',$cs_record->val('customer_id'));
				$record->setValue('site_id',$cs_record->val('site_id'));
			}			
	}



	function afterSave(&$record){
		//After saving the record, assign the selected po's assigned_voucher_id field to the record id
		if($record->val('purchase_order_id') != NULL){
			//Pull the purchase order type
			$po_type = $record->val('type');
			
			//Open record from appropriate table
			if($po_type == 'S'){
				$po_record = df_get_record('purchase_order_service', array('purchase_order_id'=>$record->val('purchase_order_id')));
			}
			elseif($po_type == 'I'){
				$po_record = df_get_record('purchase_order_inventory', array('purchase_order_id'=>$record->val('purchase_order_id')));
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
