<?php

class tables_time_logs {
	private $rate_type;

	//Permissions
	function getPermissions(&$record){
		//Check if the user is logged in & what their permissions for this table are.
		if( isUser() ){
			$userperms = get_userPerms('time_logs');
			if($userperms == "view")
				return Dataface_PermissionsTool::getRolePermissions("READ ONLY"); //Assign Read Only Permissions
			elseif($userperms == "edit"){
				//Check status, determine if record should be uneditable.
				if ( isset($record) && $record->val('status') == "Locked")
						return Dataface_PermissionsTool::getRolePermissions('NO_EDIT_DELETE');
				return Dataface_PermissionsTool::getRolePermissions(myRole()); //Assign Permissions based on user Role (typically USER)
			}
		}

		//Default: No Access
		return Dataface_PermissionsTool::NO_ACCESS();
	}	
	
	
	function block__after_custom_rph_widget(){
		$app =& Dataface_Application::getInstance();
		$record =& $app->getRecord();

		echo '	<script type="text/javascript" language="javascript">
					var elem = document.getElementById("custom_rph");
					elem.value = ' . $record->val('rate_per_hour') . '.toFixed(2);
				</script>';

		if($record->val('rate_type') != "custom") {
		//	echo '<script type="text/javascript" language="javascript">hide_widget("custom_rph");</script>';
			echo '	<script type="text/javascript" language="javascript">
						document.getElementById("custom_rph_form_row").style.display = "none";
					</script>';
			
			
		}
	}
		
	function rate_type__validate(&$record, $value, &$params){
		if(is_array($value))
			$this->rate_type = $value[0];
		else
			$this->rate_type = $value;

		return 1;
	}
		
	function beforeSave(&$record){
		if($this->rate_type == 'no_chrg')
			$record->setValue('rate_per_hour', 0.00);
		if($this->rate_type == 'custom')
			$record->setValue('rate_per_hour', $record->val('custom_rph'));
		elseif($this->rate_type != ''){		//This line was causing problems with editing entries in a call slip
//		elseif($this->rate_type == ''){		//Looked wrong anyway, so changed it to this - VERIFY -- > this line causes problems with generating payroll
			$call_rec = df_get_record('call_slips', array('call_id'=>$record->val('category')));
			$cust_rec = df_get_record('customers', array('customer_id'=>$call_rec->val('customer_id')));
			$rate_rec = df_get_record('rates', array('rate_id'=>$cust_rec->val('rate')));
			
			//echo 'rate: ' . $rate_rec->val('rate_id') . '<br>' . 'charge: ' . $rate_rec->val((string)$this->rate_type);
			
			$record->setValue('rate_id', $rate_rec->val('rate_id'));
			$record->setValue('rate_per_hour', $rate_rec->val($this->rate_type));
		}
		
		//return PEAR::raiseError("FIN",DATAFACE_E_NOTICE);
	}
	
	
	
	//This is for Call Slip Invoices
	function field__hours($record){
		$arrive = Dataface_converters_date::datetime_to_string($record->val('start_time'));
		$depart = Dataface_converters_date::datetime_to_string($record->val('end_time'));
		$hours = number_format(((strtotime($depart) - strtotime($arrive)) / 3600),1);
		return $hours;
	}
	
	function field__hour_cost($record){
		$arrive = Dataface_converters_date::datetime_to_string($record->val('start_time'));
		$depart = Dataface_converters_date::datetime_to_string($record->val('end_time'));
		$hours = number_format(((strtotime($depart) - strtotime($arrive)) / 3600),1);
		$cost = number_format($record->val('rate_per_hour') * $hours, 2);
		return $cost;
	}
	
	function field__work_date($record){
		$arrive = Dataface_converters_date::date_to_string($record->val('start_time'));
		//$hours = number_format(((strtotime($depart) - strtotime($arrive)) / 3600),1);
		return $arrive;
	}
	
}

?>
