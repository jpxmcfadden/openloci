<?php

class tables_employees {

	function getTitle(&$record){
	//	echo "!" . $record->val('employee_id') . "!";
	//	$rec = df_get_record('dataface__record_mtimes', array('recordid'=>"employees?employee_id=1"));
	//	echo "FOO: ". date('Y-m-d h:i:s', $rec->display('mtime'));
		return $record->val('first_name').' '.$record->val('last_name');
	}

	function titleColumn(){
		return 'CONCAT(last_name,", ",first_name)';
	}	

	function email__htmlValue(&$record){
		return '<a href="mailto:' . $record->strval('email') . '">' . $record->strval('email') . '</a>'; 
	}
	
	//function address__renderCell(&$record){
	//	return '<div style="white-space:nowrap">'.$record->strval('address').'</div>';
	//}
	
	//function email__renderCell( &$record ){
	//	return $record->strval('email').' ( send email)';
	//}


	//Set the hidden timestamp field to the page load time.
	function field__timestamp(&$record){
	//function timestamp__default(){
		//$recid = $record->getID();
		//$rec = df_get_record('employees', array('recordid'=>$recid));
		//echo $record->val('timestamp');
		//if(!timestamp)
		//	echo "C";
		//if($record->val('timestamp')) echo "F";
		//return time();
		//if($record->val('timestampp') == "")
		//echo $record->getPublicLink();
		
		return time();
	}
	
	//Check if the data has been saved after we have already loaded the page, before overwriting
	//If so, return an error!
	//This doesn't work yet!!:::Problems with above field__ function... always updates timestamp on load, which includes right beforeSave... so it always passes.
	function beforeSave($record){
		$recid = $record->getID();
		$rec = df_get_record('dataface__record_mtimes', array('recordid'=>$recid));
		if($rec > $record->val('timestamp')){
			//$msg = date('Y-m-d h:i:s', $rec->display('mtime'));
			//$msg = "Last Updated Time: ".$rec->display('mtime')." | Page Accessed: ".$record->val('timestamp');
			$msg = "\nThis page has been modified recently.\n\n This page was loaded @ ".date('Y-m-d h:i:s',$record->val('timestamp'))."\nThis page was modified @ ".date('Y-m-d h:i:s', $rec->display('mtime'))."\n\nPlease try again. == ".$record->val('timestamp');
			return PEAR::raiseError($msg,DATAFACE_E_NOTICE);
		}
	}


	
	//function section__more(&$record){
	//	return array(
	//		'content' => '',
	//		'class' => 'main',
	//		'label' => 'More Details',
	//		'order' => 2
	//	);
	//}

}
?>