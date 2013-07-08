<?php

class tables_customer_sites {

	function getTitle(&$record){
		return $record->val('site_address') . " (ID: " . $record->val('customer_site_id').")";
	}

//	function titleColumn(){
//		return 'address';
//	}
	
	function site_state__default(){
		return default_location_state();
	}


	
	function beforeInsert(&$record){
	
		//Assign the "Customer Site ID" field. (format xxxxx-yyyy, [customer_id]-[last_site+1])
			//Start with 0000
			$customer_site_id = "0000";

			//Pull all site records for the current customer
			$customer_site_records = df_get_records_array('customer_sites', array('customer_id'=>$record->val('customer_id')));

			//Parse through previous site records and find the highest site #
			// - In theory this *should* be the highest site_id, but we do this just in case
			foreach($customer_site_records as $customer_site_record){
				//Pull the last 4 digits
				$site_section = substr($customer_site_record->val('customer_site_id'),-4);
				
				//If new > old, old = new
				if($site_section > $customer_site_id)
					$customer_site_id = $site_section;
			}

		//Increment value & Format string
		$customer_site_id = $record->val("customer_id") . "-" . str_pad($customer_site_id + 1, 4, "0", STR_PAD_LEFT);

		//Set
		$record->setValue("customer_site_id", $customer_site_id);
	}	
	
	
	
	
	
	
	
	
	
	
//	function section__details(&$record){
//		return array(
//			'content' => '',
//			'class' => 'main',
//			'label' => 'Site Details',
//			'order' => 1
//		);
//	}
/*
	function section__more(&$record){
		$sql = "SELECT * FROM customers WHERE customer_id='".addslashes($record->val('customer_id'))."'";
		$res = mysql_query($sql, df_db());
		$row = mysql_fetch_array($res);

		$label = "Details: " . $row['customer'];
		
		ob_start();
			echo '</div><table class="record-view-table">';
			echo '<tr><th class="record-view-label">Customer ID</th><td class="record-view-value">' . $row['customer_id'] . '</td></tr>';
			echo '<tr><th class="record-view-label">Phone #</th><td class="record-view-value">' . $row['phone'] . '</td></tr>';
			echo '<tr><th class="record-view-label">Contact</th><td class="record-view-value">' . $row['contact'] . '</td></tr>';
			echo '<tr><th class="record-view-label">Balance</th><td class="record-view-value">' . $row['balance'] . '</td></tr>';
		//	echo '<tr><th class="record-view-label"> </th><td class="record-view-value">' . $row[' '] . '</td></tr>';


		//	echo "<tr><td>Phone #</td><td>" . $row['phone'] . "</td></tr>";
		//	echo "<tr><td>Contact</td><td>" . $row['contact'] . "</td></tr>";
		//	echo "<tr><td>Balance</td><td>" . $row['balance'] . "</td></tr>";
		//	echo "<tr><td></td><td>" . $row[''] . "</td></tr>";
			echo '</table><div class="dataface-view-section">';

		$content = ob_get_contents();
		ob_end_clean();

		
		@mysql_free_result($res);
		
		
		
		
		
		return array(
			'content' => $content,
			'class' => 'main',
			'label' => $label,
			'order' => 2
		);
	}
*/
}
?>