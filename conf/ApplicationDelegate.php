<?php
/**
 * A delegate class for the entire application to handle custom handling of 
 * some functions such as permissions and preferences.
 */
class conf_ApplicationDelegate {

	function getPermissions(&$record){
		if ( !isUser() )
			return Dataface_PermissionsTool::NO_ACCESS();
		return Dataface_PermissionsTool::getRolePermissions(myRole());

		//if ( isAdmin() )
		//	return Dataface_PermissionsTool::ALL();
	}

	//Create a new hidden field with the record version at the end of the form.
	function block__before_form_close_tag(){
		//Create a new hidden timestamp field with the page load time at the end of the form.
		//echo '<input name="timestamp" id="timestamp" type="hidden" value="'.time().'" data-xf-field="timestamp" />';

		//Get the current record ID
		$app =& Dataface_Application::getInstance(); 
		$record =& $app->getRecord();

		//Check if any records exist (this will only be false if there are no records in a table)
		if(isset($record)){
			//Get the record version from the record versioning table
			$v_record = df_get_record('_record_versioning', array('record_id'=>'='.$record->getID()));
		}
		
		//If a record version was found, set that, otherwise, use -1 (will be incremented to 0 upon saving).
		$version = isset($v_record) ? $v_record->val('version') : -1;
		
		//Create hidden field with the record version.
		echo '<input name="record_version" id="record_version" type="hidden" value="'.$version.'" data-xf-field="record_version" />';
	}
	
	function beforeHandleRequest() {
		$app =& Dataface_Application::getInstance(); 
		$query =& $app->getQuery();
		$record =& $app->getRecord();
		

		//Check if the request is to save a record.
		if(isset($query['--session:save'])){
			//Get the record ID
			$recid = $record->getID();

			//Get the version that is being attempted to be saved from the "record_version" field.
			$loaded_version = $_POST['record_version'];
			
			//Pull the record version from the record versioning table
			$v_record = df_get_record('_record_versioning', array('record_id'=>'='.$recid));
			
			//Check to see if the version record exists
			if(isset($v_record)){
			
				//Check to see if we're trying to save an out of date version
				if($v_record->val('version') == $loaded_version){ //If not out of date, update the record version and save the record.
					$v_record->setValue('version',($v_record->val('version')+1));
					$v_record->save(null, true);
				}
				else{ //If out of date, throw an error message and don't save the record.

					//Setting '--no-query' causes Xataface to not process the query.
					//This is a little hack to stop the record from being saved.
					$query['--no-query'] = true;
					
					//Error Message
					$msg .= "ERROR: It appears that this record has been modified since you opened it. Your changes could not be saved, and the most recently updated record has been loaded. Please re-enter your changes and try saving again.";
					
					//Redirect back to 'edit' with the error message.
					$location = $record->getURL('-action=edit'); //<-- this will take you back to the record's "view" tab when a record is saved
					header('Location: '.$record->getURL('-action=edit').'&--msg='.urlencode($msg)); //Reload the page so that the fields update.

				}
				
			}
			else{ //If the version record doesn't already exist, create a new versioning record.

				$v_record = new Dataface_Record('_record_versioning', array());
				$v_record->setValues(array('record_id'=>$recid,'version'=>0));
				$v_record->save(null, true);
				
			}
		
		}
			
			
			
		//If user is logged in, select which tables are shown
		if(isUser()){
			//If first logging in, default to the Dashboard page.
			if ( $query['-table'] == 'dashboard' and ($query['-action'] == 'browse' or $query['-action'] == 'list') ){
				$query['-action'] = 'dashboard';
			}
			
			if(!isAdmin()){
				//Makes sure that the NavMenu cannot see these tables 
				//	unset($app->_conf['_tables']['users']); 
				//Makes sure that a non-admin user cannot access the tables from the browser. 
					$app->_conf['_disallowed_tables']['admin_rule'] = '/^admin_/';	//Blocks all tables starting with "admin_" --!!right now this blocks read_only too!!
			}
			
			foreach($app->_conf['_tables'] as $values) {
			}
			
		}
		//Unset *all* tables from showing when not logged in.
		else {

			//$app =& Dataface_Application::getInstance(); 

			//Get database name from conf.ini
			//$dbname = $app->_conf[_database][name];
			
			//List all tables from database and unset them
		//	$result = mysql_query("SHOW TABLES FROM $dbname");
		//	while ($row = mysql_fetch_row($result)) {
		//		unset($app->_conf['_tables'][$row[0]]);
		//	}
		//	mysql_free_result($result);
		}
	}


	function after_action_new($params=array()){
		$record =& $params['record'];
		header('Location: '.$record->getURL('-action=view').'&--msg='.urlencode('Record successfully added.'));
		exit;
	}

	function after_action_edit($params=array()){
		//Get the record data from $params
		$record =& $params['record'];

		//Pull the "-portal-context" field out of the current record
		$app =& Dataface_Application::getInstance();
		$query =& $app->getQuery();
		$portal = $query['-portal-context'];

		//If "-portal-context" exists, this is a relationship record, otherwise it's just a regular record save
		if(isset($portal)){ //If Child Table
			//Parse out the parent table from the URL
			$s = strpos($portal,'/');
			$table = substr($portal,0,$s);
			$portal = substr($portal,$s+1);

			//Parse out the child table from the URL
			$s = strpos($portal,'?');
			$relation = substr($portal,0,$s);
			$portal = substr($portal,$s+1);

			//Parse out the parent record id from the URL
			$s = strpos($portal,'&');
			$table_id = substr($portal,0,$s);;;
	
			//Create the redirect URL from the above
			//This will take you back to the "relationship list" view of the parent record
			$location = "index.php?-table=$table&-action=related_records_list&-mode=list&-recordid=$table%3F$table_id&-relationship=$relation";
		}
		else //If Parent Record
			$location = $record->getURL('-action=view'); //<-- this will take you back to the record's "view" tab when a record is saved
		
		//Redirect to the appropriate location
		header('Location: '.$location.'&--msg='.urlencode('Record successfully updated.'));


		exit;

	}
	
	function block__custom_stylesheets(){
		echo '<link href="style.css" rel="stylesheet" type="text/css"/>';
		//echo '<link rel="stylesheet" href="print.css" type="text/css" media="print" />';
	}
	
	function block__custom_javascripts(){
		echo '<script src="javascripts.js" type="text/javascript" language="javascript"></script>';
	}	
}


?>
