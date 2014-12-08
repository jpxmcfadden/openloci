<?php

class tables__system_users {

	//Permissions
	function getPermissions(&$record){
		//Check if the user is logged in & that they are an admin.
		$perms = Dataface_PermissionsTool::getRolePermissions(myRole()); //Assign Permissions based on user Role (typically SYSTEM)
		
		//Remove the delete permission.
		$perms['delete'] = 0;
		
		if( isAdmin() ){
			return $perms;
		}

		//Default: No Access
		return Dataface_PermissionsTool::NO_ACCESS();
	}


	function valuelist__menu(){
		//Get query to determine the mode
		$app = Dataface_Application::getInstance();
		$query = $app->getQuery();
		
		//If the user is in view / list mode, show "-" for null, otherwise show "No Access"
		if ( $query['-table'] == '_system_users' and ($query['-action'] == 'view' or $query['-action'] == 'list') )
			return array(null=>'-', 'view'=>'Viewable');
			
		return array(null=>'No Access', 'view'=>'Viewable');
	}
	
	function valuelist__view_edit(){
		//Get query to determine the mode
		$app = Dataface_Application::getInstance();
		$query = $app->getQuery();

		//If the user is in view / list mode, show "-" for null, otherwise show "No Access"
		if ( $query['-table'] == '_system_users' and ($query['-action'] == 'view' or $query['-action'] == 'list') )
			return array(null=>'-', 'view'=>'View', 'edit'=>'Edit');
			
		return array(null=>'No Access', 'view'=>'View', 'edit'=>'Edit');
	}
	
	function valuelist__view_edit_post(){
		//Get query to determine the mode
		$app = Dataface_Application::getInstance();
		$query = $app->getQuery();

		//If the user is in view / list mode, show "-" for null, otherwise show "No Access"
		if ( $query['-table'] == '_system_users' and ($query['-action'] == 'view' or $query['-action'] == 'list') )
			return array(null=>'-', 'view'=>'View', 'edit'=>'Edit', 'post'=>'Post');
			
		return array(null=>'No Access', 'view'=>'View', 'edit'=>'Edit', 'post'=>'Post');
	}

	function valuelist__view_edit_receive_post(){
		//Get query to determine the mode
		$app = Dataface_Application::getInstance();
		$query = $app->getQuery();

		//If the user is in view / list mode, show "-" for null, otherwise show "No Access"
		if ( $query['-table'] == '_system_users' and ($query['-action'] == 'view' or $query['-action'] == 'list') )
			return array(null=>'-', 'view'=>'View', 'edit'=>'Edit', 'receive'=>'Receive', 'post'=>'Post');
			
		return array(null=>'No Access', 'view'=>'View', 'edit'=>'Edit', 'post'=>'Post');
	}
	
	function valuelist__view_edit_post_close(){
		//Get query to determine the mode
		$app = Dataface_Application::getInstance();
		$query = $app->getQuery();

		//If the user is in view / list mode, show "-" for null, otherwise show "No Access"
		if ( $query['-table'] == '_system_users' and ($query['-action'] == 'view' or $query['-action'] == 'list') )
			return array(null=>'-', 'view'=>'View', 'edit'=>'Edit', 'post'=>'Post', 'close'=>'Close');
			
		return array(null=>'No Access', 'view'=>'View', 'edit'=>'Edit', 'post'=>'Post', 'close'=>'Close');
	}

	function valuelist__employee_list(){
		//Get query to determine the mode
		$app = Dataface_Application::getInstance();
		$query = $app->getQuery();

		//If the user is in view / list mode, show "-" for null, otherwise show "No Access"
		if ( $query['-table'] == '_system_users' and ($query['-action'] == 'view' or $query['-action'] == 'list') )
			return array(null=>'-', 'view_techs'=>'View Technicians', 'view_all'=>'View All Employees', 'edit'=>'Edit');
			
		return array(null=>'No Access', 'view_techs'=>'View Technicians Only', 'view_all'=>'View All Employees', 'edit'=>'Edit');
	}
	
}

?>