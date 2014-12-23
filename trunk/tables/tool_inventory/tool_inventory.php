<?php

class tables_tool_inventory {


	//Permissions
	function getPermissions(&$record){
		//Check if the user is logged in & what their permissions for this table are.
		if( isUser() ){
			$userperms = get_userPerms('inventory');
			if($userperms == "view")
				return Dataface_PermissionsTool::getRolePermissions("READ ONLY"); //Assign Read Only Permissions
			elseif($userperms == "edit" || $userperms == "overide"){
				$perms = Dataface_PermissionsTool::getRolePermissions(myRole()); //Assign Permissions based on user Role (typically USER)
				unset($perms['delete']);
				return $perms;
			}		}

		//Default: No Access
		return Dataface_PermissionsTool::NO_ACCESS();
	}

	
	function block__before_record_actions(){
		$app =& Dataface_Application::getInstance(); 
	//	$record =& $app->getRecord();

		if(get_userPerms('inventory') == "overide")
			echo '	<div class="dataface-view-record-actions">
						<ul>
							<li id="inventory_overide" class="plain">
								<a class="" id="inventory_overide-link" href="'.$app->url('-action=inventory_overide').' title="" data-xf-permission="view">
									<img id="inventory_overide-icon" src="images/report_icon.png" alt="Adjust Quantity">
									<span class="action-label">Adjust Quantity</span>
								</a>
							</li>
						</ul>
					</div>';

		//Prompt.
		echo '	<script>
					jQuery("#inventory_overide").click(function(){
						return confirm("NOTICE: You are about to edit the inventory quantity. Do you wish to proceed?");
					});
				</script>';
	}
	
	
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
			$purchase_history_records = $record->getRelatedRecords('purchase_history');

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