<?php

class tables_rates {

	//Permissions
	function getPermissions(&$record){
		//Check if the user is logged in & what their permissions for this table are.
		if( isUser() ){
			$userperms = get_userPerms('rates');
			if($userperms == "view")
				return Dataface_PermissionsTool::getRolePermissions("READ ONLY"); //Assign Read Only Permissions
			elseif($userperms == "edit")
				return Dataface_PermissionsTool::getRolePermissions(myRole()); //Assign Permissions based on user Role (typically USER)
		}

		//Default: No Access
		return Dataface_PermissionsTool::NO_ACCESS();
	}

/*	function block__after_rate_label_widget(){
		echo '<table class="rate_codes"><tr><td>';
	}

	function block__after_supr_tt_widget(){
		echo '</td></tr></table>';
	}
*/

/*	function rate_data__renderCell(&$record){
		$result = '<table class="rate_codes">';
		foreach ( $record->val('rate_data') as $vals){ $result .= '<tr><th>' . $vals['type'] . '</th><th>' . $vals['rate'] . '</th></tr>'; };
		$result .= "</table>";

		return $result;
	}

	function rate_data__htmlValue(&$record){
		$result = '<table class="rate_codes">';
		foreach ( $record->val('rate_data') as $vals){ $result .= '<tr><th>' . $vals['type'] . '</th><th>' . $vals['rate'] . '</th></tr>'; };
		$result .= "</table>";

		return $result;
	}
*/	
	
}

?>