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
	
	
	
}
?>