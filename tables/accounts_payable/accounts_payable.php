<?php

class tables_accounts_payable {

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
		$po_rec = df_get_record('purchase_orders', array('purchase_id'=>$record->val('purchase_order_id')));
		$po_rec->setValue('assigned_voucher_id',$record->val('voucher_id'));
		//$gen_inv->save(null, true);
		$po_rec->save();
		//return PEAR::raiseError('END',DATAFACE_E_NOTICE);
	}
}
?>