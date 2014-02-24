<?php

class tables_employees {

	//Set timelog edit permissions to use the timelog table's permission settings (unset related record permissions)
	function rel_time_logs__permissions(&$record){
		$perms = &Dataface_PermissionsTool::getRolePermissions(myRole());
		unset($perms['edit related records']);
		unset($perms['delete related record']);
	}

//function getPermissions($record){
//	$perms = &Dataface_PermissionsTool::getRolePermissions(myRole());
//	unset($perms['edit related records']);
//	unset($perms['delete related record']);
//	return Dataface_PermissionsTool::getRolePermissions(myRole());
//}	

	function getTitle(&$record){
		return $record->val('first_name').' '.$record->val('last_name');
	}

	function titleColumn(){
		return 'CONCAT(last_name,", ",first_name)';
	}

	function email__htmlValue(&$record){
		return '<a href="mailto:' . $record->strval('email') . '">' . $record->strval('email') . '</a>'; 
	}
	

	//DEFAULT VALUES
	function state__default(){
		return default_location_state();
	}

//	function regular_hours__default(){
//		return "40.00";
//	}

	function full_part__default(){
		return "FT";
	}

//	function pay_period__default(){
//		return "WK";
//	}

	function direct_deposit__default(){
		return "N";
	}

	function exemptions_federal__default(){
		return "0";
	}

	function exemptions_state__default(){
		return "0";
	}

	function exemptions_city__default(){
		return "0";
	}

	function active__default(){
		return "Y";
	}
	
	
	//Create a new hidden timestamp field with the page load time at the end of the form.
	//function block__before_form_close_tag(){
	//	echo '<input name="timestamp" id="timestamp" type="hidden" value="'.time().'" data-xf-field="timestamp" />';
	//}

	//Check if the data has been saved after we have already loaded the page, before overwriting, if so, return an error!
	function beforeSave(&$record){
	/*	$recid = $record->getID(); //Get the record ID
		$rec = df_get_record('dataface__record_mtimes', array('recordid'=>$recid)); //Pull the last modified time from the dataface record
		$timestamp = $_POST['timestamp'];
		if($rec->display('mtime') > $timestamp) //Check to see if the last modified time is greater than the page load time
		{
			$msg = "ERROR: It appears that someone has recently modified this record, and your changes could not be saved. Here is the current record. Please re-enter your changes and try saving again.";
			header('Location: '.$record->getURL('-action=edit').'&--msg='.urlencode($msg)); //Reload the page so that the fields update.
			return PEAR::raiseError('',DATAFACE_E_NOTICE); //Return an error and don't save the record.
		}
		*/
	}

	function afterSave(&$record)
	{
	//	echo '<script type="text/javascript" language="javascript">alert("saved!");</script>';
	}

	//function section__more(&$record){
	//	return array(
	//		'content' => '',
	//		'class' => 'main',
	//		'label' => 'More Details',
	//		'order' => 2
	//	);
	//}

	//function field__timestamp(&$record){
	//function address__renderCell(&$record){
	//	return '<div style="white-space:nowrap">'.$record->strval('address').'</div>';
	//}
	//function email__renderCell( &$record ){
	//	return $record->strval('email').' ( send email)';
	//}

	
}
?>