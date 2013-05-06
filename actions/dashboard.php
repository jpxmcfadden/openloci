<?php
class actions_dashboard {
	function handle(&$params){

		if(1){
			$jpp = df_get_records_array('general_ledger', array('post_status'=>'Pending'));
			foreach($jpp as $i=>$record){}
			df_display(array("journal_pending"=>($i+1)), 'dashboard.html');
		}
		
		if(0){
			$css = df_get_records_array('call_slips', array('status'=>'RDY'));
			foreach($css as $i=>$record){}
			df_display(array("call_slips"=>($i+1)), 'dashboard_service.html');
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