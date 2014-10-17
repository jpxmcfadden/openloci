<?php

class tables_customer_markup {

	//Permissions
	function getPermissions(&$record){
		//Check if the user is logged in & what their permissions for this table are.
		if( isUser() ){
			$userperms = get_userPerms('markup');
			$perms = Dataface_PermissionsTool::getRolePermissions(myRole()); //Assign Permissions based on user Role (typically USER)
			$perms['delete'] = 0;
			
			if($userperms == "view")
				return Dataface_PermissionsTool::getRolePermissions("READ ONLY"); //Assign Read Only Permissions
			elseif($userperms == "edit")
				return $perms;
		}

		//Default: No Access
		return Dataface_PermissionsTool::NO_ACCESS();
	}

	function section__markup_rates(&$record){
		$childString = "";
		
		//Get all associated markup rates
		$rate_records = df_get_records_array('customer_markup_rates',array('markup_id'=>$record->val('markup_id')));
		
		$childString .= '<table class="view_add"><tr>
							<th>From</th>
							<th>To</th>
							<th>Percent</th>
						</tr>';

		
		foreach($rate_records as $rate){
			$childString .= '<tr><td>'. $rate->val('from') . '</td><td>' . $rate->val('to') . '</td><td>' . $rate->val('markup_percent') . '</td></tr>';
		}
		
		$childString .= "</table>";
	
		return array(
			'content' => "$childString",
			'class' => 'main',
			'label' => 'Markup Rates',
			'order' => 10
		);
	}
	
}

?>