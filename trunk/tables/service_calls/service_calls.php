<?php

class tables_service_calls {

	function getBgColor($record){
		if ( $record->val('tech_id') == 1) return 'blue';
		if ( $record->val('tech_id') == 2) return 'blueviolet';
		if ( $record->val('tech_id') == 3) return 'brown';
		if ( $record->val('tech_id') == 4) return 'cadetblue';
		if ( $record->val('tech_id') == 5) return 'chocolate';
		if ( $record->val('tech_id') == 6) return 'cornflowerblue';
		if ( $record->val('tech_id') == 7) return 'crimson';
		if ( $record->val('tech_id') == 8) return 'darkcyan';
		if ( $record->val('tech_id') == 9) return 'darkred';
		if ( $record->val('tech_id') == 10) return 'green';
		if ( $record->val('tech_id') == 11) return 'goldenrod';
		else return 'rgb(0,0,0)';
	}
	
	function calendar__decorateEvent(Dataface_Record $record, &$event){
		$rec_site = df_get_record('sites', array('site_id'=>$record->val('site_id')));
		$rec_empl = df_get_record('employees', array('employee_id'=>$record->val('tech_id')));
		$event['title'] = "\nTech: " . $rec_empl->val('first_name') . " " . $rec_empl->val('last_name') . "\nSite: " . $rec_site->val('address');
	}

	function init() {
	//	echo "foo";
	//	$app =& Dataface_Application::getInstance(); 
	//	$app->_conf['_modules'] = 'modules_calendar=modules/calendar/calendar.php';
	}
	
}
?>