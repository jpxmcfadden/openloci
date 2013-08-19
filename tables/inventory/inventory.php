<?php

class tables_inventory {


	function getPermissions(&$record){
	//	return Dataface_PermissionsTool::NO_EDIT_DELETE();
		$role = "ACCESS";
		return Dataface_PermissionsTool::getRolePermissions($role);
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

	function quantity__display(&$record){
		$quantity = explode('.',$record->val('quantity'));
		if($quantity[1] != 0)
			$quantity[1] = '.'.$quantity[1];
		else
			$quantity[1] = '';
			
		return $quantity[0] . $quantity[1];
	}
	
	function beforeSave(&$record){
		//Calculate and Save Average
			$purchase_history_records = $record->getRelatedRecords('inventory_purchase_history');

			$purchase_total = 0;
			$count = 0;

			foreach($purchase_history_records as $purchase_history_record){
				$count++;
				$purchase_total += $purchase_history_record['purchase_price'];
				if($count == 10)
					break;
			}

			if($count > 0){
				$average = number_format($purchase_total / $count, 2);
				$record->setValue('average_purchase',$average);
			}

	}
	
	function beforeInsert(&$record){
			$record->setValue('last_purchase',0);
			$record->setValue('average_purchase',0);
	}
	
}

?>