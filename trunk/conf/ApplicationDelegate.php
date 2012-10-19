<?php
/**
 * A delegate class for the entire application to handle custom handling of 
 * some functions such as permissions and preferences.
 */
class conf_ApplicationDelegate {
    /**
     * Returns permissions array.  This method is called every time an action is 
     * performed to make sure that the user has permission to perform the action.
     * @param record A Dataface_Record object (may be null) against which we check
     *               permissions.
     * @see Dataface_PermissionsTool
     * @see Dataface_AuthenticationTool
     */
    function getPermissions(&$record){
        if ( !isUser() ) return Dataface_PermissionsTool::NO_ACCESS();
        return Dataface_PermissionsTool::getRolePermissions(myRole());
    }


	/*//Fix? for user:shannah's article on dashboard... see: http://xataface.com/forum/viewtopic.php?t=4739
	function beforeHandleRequest(){
      $auth =& Dataface_AuthenticationTool::getInstance();
            $user =& $auth->getLoggedInUser();
      if ( !isset($user) ) return Dataface_PermissionsTool::NO_ACCESS();
           $app =& Dataface_Application::getInstance();
           $query =& $app->getQuery();
           if ( $query['-table'] == 'dashboard' ){
               $query['-action'] = 'dashboard';
           }
	*/
	
	
	//Define which which tables are shown in the list.
	function beforeHandleRequest() {
		//If user is logged in, select which tables are shown
		if(isUser()){
			$app =& Dataface_Application::getInstance(); 

			//If first logging in, default to the Dashboard page.
			$query =& $app->getQuery();
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

			$app =& Dataface_Application::getInstance(); 

			//Get database name from conf.ini
			$dbname = $app->_conf[_database][name];
			
			//List all tables from database and unset them
			$sql = "SHOW TABLES FROM $dbname";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_row($result)) {
				unset($app->_conf['_tables'][$row[0]]);
			}
			mysql_free_result($result);
		}
	}

//I don't think this works in here... I think it may need to be in each tables delegate class
	function beforeInsert($record){
		$username = Dataface_AuthenticationTool::getInstance()->getLoggedInUserName();
		$record->setValue('creator', $username);
	}

	function block__custom_javascripts(){
		echo '<script src="javascripts.js" type="text/javascript" language="javascript"></script>';
	}	
	
	function after_action_new($params=array()){
		$record =& $params['record'];
		header('Location: '.$record->getURL('-action=view').'&--msg='.urlencode('Record successfully added.'));
		exit;
	}

	function after_action_edit($params=array()){
		$record =& $params['record'];
		header('Location: '.$record->getURL('-action=view').'&--msg='.urlencode('Record successfully updated.'));
		exit;
	}
	
}


?>
