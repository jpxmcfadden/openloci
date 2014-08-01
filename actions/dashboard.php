<?php
class actions_dashboard {
	function handle(&$params){

		//Permission check
		if ( !isUser() )
			return Dataface_Error::permissionDenied("You are not logged in");

		//Get user record to determine what to display
		$user_record = df_get_record('_system_users', array('user_id'=>userID())); 
		$dashboard_display_info = $user_record->vals(); //Convert to standard array
		
		//Unset irrelevant information
		unset($dashboard_display_info['user_id']);
		unset($dashboard_display_info['username']);
		unset($dashboard_display_info['password']);
		//unset($dashboard_display_info['role']);
		//echo "<pre>";print_r($dashboard_display_info);echo "</pre>";

		//Check permissions and get data from table if appropriate.
			if($dashboard_display_info['call_slips'] == "edit"){
				$status = df_get_records_array('call_slips', array('status'=>'RDY'));
				$dashboard_display_info['call_slips_ready'] = count($status);
			}

			if($dashboard_display_info['general_ledger'] == "post" || $dashboard_display_info['general_ledger'] == "close"){
				$status = df_get_records_array('general_ledger', array('post_status'=>'Pending'));
				$dashboard_display_info['general_ledger_pending'] = count($status);
			}

			if($dashboard_display_info['purchase_order_inventory'] == "post"){
				$status = df_get_records_array('purchase_order_inventory', array('post_status'=>'Pending'));
				$dashboard_display_info['ipo_pending'] = count($status);
			}
			if($dashboard_display_info['purchase_order_service'] == "post"){
				$status = df_get_records_array('purchase_order_service', array('post_status'=>'Pending'));
				$dashboard_display_info['spo_pending'] = count($status);
			}
			if($dashboard_display_info['purchase_order_office'] == "post"){
				$status = df_get_records_array('purchase_order_office', array('post_status'=>'Pending'));
				$dashboard_display_info['opo_pending'] = count($status);
			}
			if($dashboard_display_info['purchase_order_rendered_services'] == "post"){
				$status = df_get_records_array('purchase_order_rendered_services', array('post_status'=>'Pending'));
				$dashboard_display_info['rpo_pending'] = count($status);
			}

			if($dashboard_display_info['purchase_order_rendered_services'] == "post"){
				$status = df_get_records_array('accounts_payable', array('post_status'=>'Pending', 'batch_id'=>'='));
				$dashboard_display_info['payable_pending'] = count($status);
				
				$status = df_get_records_array('accounts_payable_batch', array('post_status'=>'='));
				$dashboard_display_info['payable_pending_batches'] = count($status);
			}
			
		//Display Dashboard
		df_display($dashboard_display_info, 'dashboard.html');
		


	/*
		$app =& Dataface_Application::getInstance(); 
		//Get database name from conf.ini
		$dbname = $app->_conf[_database][name];		

		$sql = "SHOW TABLES FROM $dbname";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_row($result)) {
			if (strpos($row[0],'admin') === FALSE && strpos($row[0],'dataface') === FALSE)
				echo $row[0]."<br>";
		}
		mysql_free_result($result);
	*/
	}
}
