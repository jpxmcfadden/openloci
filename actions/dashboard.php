<?php
class actions_dashboard {
	function handle(&$params){

		if(1){
			$jpp = df_get_records_array('general_ledger', array('post_status'=>'Pending'));
			$ipo = df_get_records_array('purchase_order_inventory', array('post_status'=>'Pending'));
			$spo = df_get_records_array('purchase_order_service', array('post_status'=>'Pending'));
			$opo = df_get_records_array('purchase_order_office', array('post_status'=>'Pending'));
			$rpo = df_get_records_array('purchase_order_rendered_services', array('post_status'=>'Pending'));
			$app = df_get_records_array('accounts_payable', array('post_status'=>'Pending', 'batch_id'=>'='));
			$apb = df_get_records_array('accounts_payable_batch', array('post_status'=>'='));
			$css = df_get_records_array('call_slips', array('status'=>'RDY'));
			df_display(array("call_slips"=>(count($css)),"payable_pending_batches"=>(count($apb)),"payable_pending"=>(count($app)),"journal_pending"=>(count($jpp)),"ipo_pending"=>(count($ipo)),"spo_pending"=>(count($spo)),"opo_pending"=>(count($opo)),"rpo_pending"=>(count($rpo))), 'dashboard.html');
		}
		if(0){
		
			$css = df_get_records_array('call_slips', array('status'=>'RDY'));
			df_display(array("call_slips"=>(count($css))), 'dashboard_service.html');
			//foreach($css as $i=>$record){}
			//df_display(array("call_slips"=>($i+1)), 'dashboard_service.html');
		}

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