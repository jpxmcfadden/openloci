<?php

class tables_contracts {

	function getTitle(&$record){
	//	return $record->val('contract_type') . " Contract: " . $record->val('customer') . " - " . $record->val('address');
		return $record->val('contract_type') . " Contract: " . $record->val('address');
	}

	function titleColumn(){
		return 'CONCAT(customer," - ",address)';
	}	


	function contract_type__default(){
		return "Preventative Maintenance";
	}

//	function section__details(&$record){
//		return array(
//			'content' => '',
//			'class' => 'main',
//			'label' => 'Site Details',
//			'order' => 1
//		);
//	}

}
?>