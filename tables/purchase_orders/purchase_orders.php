<?php

class tables_purchase_orders {

	function valuelist__checkbox(){
		return array(0=>'', 1=>'Yes');
	}

	//Add attitional details to the view tab
	function section__item_list(&$record){

		$childString = "";

			//Materials
			$childString .= '<b><u>Item List</u></b><br><br>';
			$childString .= '<table class="view_add"><tr><th>Item</th><th>Quantity</th><th>Quantity Received</th><th>List Cost</th><th>Sale Cost</th></tr>';

			$purchaseorderRecords = $record->getRelatedRecords('purchase_order_list');
			foreach ($purchaseorderRecords as $cs_pr){
				$childString .= '<tr><td>' . $cs_pr['item_name'] .
								'</td><td style="text-align: right">' . $cs_pr['quantity'] .
								'</td><td style="text-align: right">' . $cs_pr['quantity_received'] .
								'</td><td style="text-align: right">' . $cs_pr['cost_list'] .
								'</td><td style="text-align: right">' . $cs_pr['cost_sale'] .
								'</td></tr>';
			}

			$childString .= '</table><br>';

		return array(
			'content' => "$childString",
			'class' => 'main',
			//'class' => 'left',
			'label' => 'Item List',
			'order' => 2
		);
	}
	
	//This is for Call Slip Invoices
	//function field__foo($record){
	//	$foo = 1;
	//	return $foo;
	//}
	
}

?>