<?php

class tables_vendors {

	//Permissions
	function getPermissions(&$record){
		//Check if the user is logged in & what their permissions for this table are.
		if( isUser() ){
			$userperms = get_userPerms('vendors');
			if($userperms == "view")
				return Dataface_PermissionsTool::getRolePermissions("READ ONLY"); //Assign Read Only Permissions
			elseif($userperms == "edit")
				return Dataface_PermissionsTool::getRolePermissions(myRole()); //Assign Permissions based on user Role (typically USER)
		}

		//Default: No Access
		return Dataface_PermissionsTool::NO_ACCESS();
	}

	function getTitle(&$record){
		return $record->val('vendor') . " (ID: " . $record->val('vendor_id') . ")";
	}

	function titleColumn(){
	//	return 'address';
	}
	
	function physical_state__default(){
		return "FL";
	}

	function block__before_rec_1099_widget(){
		$app =& Dataface_Application::getInstance(); 
		$record =& $app->getRecord();
		
		//If not checked, hide tax_id field
		if($record->val('rec_1099') != 1)
			echo '<style>#tax_id_form_row{display: none}</style>';
		
		//Load JS to hide widget
		//Dataface_JavascriptTool::getInstance()->import('widget_hide.js'); //presumably this gets loaded at the end, so "widget_hide" function is undefined at this point.
		echo '<script src="js/widget_hide.js" type="text/javascript" language="javascript"></script>';
		echo "<script>widget_hide('.checkbox-of-rec_1099','#tax_id_form_row','#vendors-tax_id-label-wrapper');</script>";		
	}	
	
	
	
	
	function section__contact(&$record){
		$childString = "";
		
		//Vendor Contacts
			$childString .= '<table class="view_contacts"><tr>
								<th>Contact</th>
								<th>Title</th>
								<th>Phone</th>
								<th>Email</th>
							</tr>';

			$contactRecords = $record->getRelatedRecords('vendor_contacts');
			foreach ($contactRecords as $contact){
				$childString .= '<tr><td>' . $contact['contact_name'] .
								'</td><td>' . $contact['contact_title'] .
								"</td><td>" . $contact['contact_phone'] .
								"</td><td>" . $contact['contact_email'] .
								"</td></tr>";
			}
		
			$childString .= '</table><br>';
		
		
		
		
		return array(
			'content' => "$childString",
			'class' => 'main',
			'label' => 'Contact Information',
			'order' => 1
		);
	}	
	
	
	
	
	function tax_id__validate( &$record, $value, &$params=array()){
		//If field is empty and the requires1099 checkbox has been checked.
		if( $value=="" && $record->_values['rec_1099'][1] == 1 ){
            $params['message'] = "You have selected \"Requires 1099\", but provided no Tax ID / SSN.";
            return false;
		}

		return true;
	}

	function beforeSave(&$record){
		//If the Remittance Address is left blank, copy the Physical Address onsave.
		if($record->val("remittance_address") == ""){
			$record->setValue("remittance_address", $record->val("physical_address"));
			$record->setValue("remittance_city", $record->val("physical_city"));
			$record->setValue("remittance_state", $record->val("physical_state"));
			$record->setValue("remittance_zip", $record->val("physical_zip"));
		}
	
	}
}
?>