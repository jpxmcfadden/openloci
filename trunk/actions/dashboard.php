<?php
class actions_dashboard {
	function handle(&$params){

		df_display(array(), 'dashboard.html');

	//	$css = df_get_records_array('call_slips', array('status'=>'RDY'));
	//	foreach($css as $i=>$record){}

	//	df_display(array("call_slips"=>($i+1)), 'dashboard_service.html');

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