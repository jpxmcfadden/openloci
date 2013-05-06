<?php

class actions_invoice {
	
	function handle(&$params){
		//$app =& Dataface_Application::getInstance();
		//$query =& $app->getQuery();

			//Extend the limit of df_get_records_array past the usual first 30 records, and query the records where [status == "RDY"]
		//	$query =& $app->getQuery();
		//	$query['-skip'] = 0;
		//	$query['-limit'] = 10000;
		//	$query['status'] = 'RDY';

		//$query['status'] = 'RDY';
		//print_r($query);
			//Pull all records that coorespond to the selected month billing cycle.
			//$records = df_get_records_array('call_slips', array('status'=>'RDY'));
			//$records = df_get_records_array('call_slips', $query);

			//Generate invoices / headers for all pulled records
			//foreach($records as $i=>$record){
				//Pull all relevant information
			//	$customer = df_get_record('customers', array('customer_id'=>$record->val('customer_id')));
			//	$customer = $customer->vals();
	
		//Permission check
		if ( !isUser() )
			return Dataface_Error::permissionDenied("You are not logged in");
			//header('Location: index.php?-action=login_prompt'); //Go to dashboard.


	}
}


?>