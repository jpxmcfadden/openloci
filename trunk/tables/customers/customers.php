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
	
	function state__default(){
		return "FL";
	}

	
}
?>