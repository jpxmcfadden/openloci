<?php
class actions_dashboard {
	function handle(&$params){
	//	$bibs = df_get_records_array('customers', array());
	//	df_display(array('customers'=>$bibs), 'dashboard.html');
		df_display(array(), 'dashboard_service.html');

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