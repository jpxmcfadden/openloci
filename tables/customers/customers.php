<?php

class tables_customers {


	function getTitle(&$record){
		return $record->val('customer');
	}

/*
	function titleColumn(){
		return 'address';
	}
*/
	
	function billing_state__default(){
		return default_location_state();
	}

	
}
?>