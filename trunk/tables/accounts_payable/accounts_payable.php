<?php

class tables_accounts_payable {

//SQL to create VIEW: (SELECT purchase_order_id, assigned_voucher_id, vendor_id FROM `purchase_order_inventory` WHERE assigned_voucher_id IS NULL) UNION (SELECT purchase_order_id, assigned_voucher_id , vendor_id FROM `purchase_order_service` WHERE assigned_voucher_id IS NULL)


	function getTitle(&$record){
		return 'Invoice ID: ' . $record->val('invoice_id') . ' - Status: ' . $record->val('status');
	}

	function titleColumn(){
		return 'CONCAT("Invoice ID: ", invoice_id," - Status: ",status)';
	}	

	function voucher_date__default(){
		return date('Y-m-d');
	}

	//function status__default(){
	//	return "OPEN";
	//}

	//function status__renderCell(&$record){
	//	return '<b>'.$record->strval('status').'</b>';
	//}
	
/*
	function email__htmlValue(&$record){
		return '<a href="mailto:' . $record->strval('email') . '">' . $record->strval('email') . '</a>'; 
	}
	
	//function email__renderCell( &$record ){
	//	return $record->strval('email').' ( send email)';
	//}


	
	function section__more(&$record){
		return array(
			'content' => '',
			'class' => 'main',
			'label' => 'More Details',
			'order' => 2
		);
	}
*/

	function afterSave(&$record){
		
		
		$po_rec = df_get_record('accounts_payable_unassigned_purchase_orders', array('purchase_order_id_full'=>$record->val('purchase_order_id')));
		$po_rec->setValue('assigned_voucher_id',$record->val('voucher_id'));
		//$gen_inv->save(null, true);
		$po_rec->save();
		//return PEAR::raiseError('END',DATAFACE_E_NOTICE);
	}
}
?>
