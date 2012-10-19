<?php

class tables_sites {

	function getTitle(&$record){
		return $record->val('address');
	}

	function titleColumn(){
		return 'address';
	}
	
	function state__default(){
		return "FL";
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