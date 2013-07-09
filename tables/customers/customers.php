<?php

class tables_customers {


	function getTitle(&$record){
		return $record->val('customer') . " (ID: " . $record->val('customer_id') . ")";
	}

/*
	function titleColumn(){
		return 'address';
	}
*/
	
	function billing_state__default(){
		return default_location_state();
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

			$contactRecords = $record->getRelatedRecords('customer_contacts');
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
	
	function block__after_markup_widget(){
	//	echo '<br><br><input id="create_new" type=checkbox>Create a site from the billing address.';
		echo '<input type="hidden" name="create_new_site" value="" data-xf-field="create_new_site">';
	//	echo '<input style="display:none" name="create_new_site" data-xf-field="create_new_site">';
	}
	
	function afterInsert(&$record){
		$new = $_POST['create_new_site'];
		if($new == "yes"){
			$site_record = new Dataface_Record('customer_sites', array());
			$site_record->setValues(array(
				'customer_id'=>$record->val('customer_id'),
				'site_address'=>$record->val('billing_address'),
				'site_city'=>$record->val('billing_city'),
				'site_state'=>$record->val('billing_state'),
				'site_zip'=>$record->val('billing_zip'),
				'site_phone'=>$record->val('cust_phone'),
				'site_fax'=>$record->val('cust_fax')
			));
			$res = $site_record->save();   // Doesn't check permissions
			//  $res = $record->save(null, true);  // checks permissions
		}
		
		
		//return PEAR::raiseError("|".$new."|",DATAFACE_E_NOTICE);
	}
	
	//function markup__validate( &$record, $value, $params=array()){
	//	return false;
	//}
	function block__custom_javascripts(){
		Dataface_JavascriptTool::getInstance()->import('confirm_new_site.js');
	}	
	
}
?>