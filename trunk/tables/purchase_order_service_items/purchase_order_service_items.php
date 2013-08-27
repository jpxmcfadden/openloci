<?php

class tables_purchase_order_service_items {

	//Set permissions on all fields to "no new / edit" if not coming from the "purchase_order_service" record.
	function __field__permissions($record){
		$app =& Dataface_Application::getInstance(); 
		$query =& $app->getQuery();
		
		if($query["-table"] != "purchase_order_service")
			return array('edit'=>0, 'new'=>0);
		
		return array();
	}
	
	//Allow editing of this field by all - used in call slips
	function quantity_used__permissions($record){
		return array('edit'=>1);
	}
	
	//Allow editing of this field by all - used in call slips
	function sale_price__permissions($record){
		return array('edit'=>1);
	}

}

?>
