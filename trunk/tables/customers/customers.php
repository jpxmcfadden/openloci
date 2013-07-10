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
		//Create a hidden field to determine if we are going to auto-generate a site on save.
		echo '<input type="hidden" name="create_new_site" value="" data-xf-field="create_new_site">';
	}
	
	function afterInsert(&$record){
		//If new customer is created, check to see if the user wants to auto-generate a new site based on the entered information.
	
		//Check if the user pressed "yes"
		$new = $_POST['create_new_site'];
		if($new == "yes"){
			//Create the new site record.
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
			//$res = $record->save(null, true);  // checks permissions
			
			//Create the new associated contact records. -- This isn't working yet. Presumably b/c the "customer contacts" have not yet been saved, thus returning an empty array.
			//$customer_contacts = $record->getRelatedRecords('customer_contacts',0,0,"customer_id=$record->val('customer_id')");
			//$customer_contacts = $record->getRelatedRecords('customer_contacts'); print_r($customer_contacts);
			//foreach ($customer_contacts as $contact_record){
			//	$site_contact_record = new Dataface_Record('customer_site_contacts', array());
			//	$site_contact_record = $contact_record;
			//	print_r($contact_record); echo "<br><br>";
			//	//$res = $site_contact_record->save();   // Doesn't check permissions
			//}
		}
		//return PEAR::raiseError("|".$new."|",DATAFACE_E_NOTICE);
	}
	
	function block__custom_javascripts(){
		Dataface_JavascriptTool::getInstance()->import('confirm_new_site.js');
	}	
	
}
?>