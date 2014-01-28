<?php
class actions_generate_payroll {
	function handle(&$params){

		//Permission check
		if ( !isUser() )
			return Dataface_Error::permissionDenied("You are not logged in");	
	
		//Pull selected date if submitted, else null.
		$selected_date = isset($_GET['selected_date']) ? $_GET['selected_date'] : null;
		
		//Pull Payroll Configuration Information
		$payroll_config = df_get_record('_system_config', array('config_id'=>1));
		$payroll_config = $payroll_config->vals();


		//If data has not been submitted, or there was a problem with the submitted date - Display selection menu
		if($selected_date == null){
			//Auto Select - last week
			$default_date = date('Y/m/d', strtotime('last '.$payroll_config['week_start'], time())); //This most recent (week start) day
			$default_date = date('Y/m/d', strtotime('last '.$payroll_config['week_start'], strtotime($default_date))); //Previous (week start) day
			
			//Display Selection Page
			df_display(array("default_date" => $default_date), 'generate_payroll.html');
		}
		else{
			//First: Check to make sure date selected is on [week_start] day & that payroll for that date has not already been done.
			$period_record = df_get_record('payroll_period', array("period_start" => $selected_date));
			if($period_record != null){
				$msg = "ERROR: You have already done payroll for the date: $selected_date";
				header('Location: index.php?-action=generate_payroll'.'&--msg='.urlencode($msg)); //Go to dashboard.
				return 0;
			}
			else if(date("l", strtotime($selected_date)) != $payroll_config['week_start']){
				$msg = "ERROR: The selected date: $selected_date, is a " . date("l", strtotime($selected_date)) . ". The system is configured to start payroll on " . $payroll_config['week_start'] . "s. Please check and change the date accordingly.";
				header('Location: index.php?-action=generate_payroll'.'&--msg='.urlencode($msg)); //Go to dashboard.
				return 0;
			}
		
			//Get payroll period start date
			$period_start = $selected_date;
			
			//Calculate period end date
			if($payroll_config['payroll_period'] == "weekly")
				$period_end = date("Y/m/d", strtotime($period_start . " + 6 days"));
			else if($payroll_config['payroll_period'] == "2weeks")
				$period_end = date("Y/m/d", strtotime($period_start . " + 13 days"));			
			else
				echo "Error!";

			$payroll_period_record = new Dataface_Record('payroll_period', array());
			$payroll_period_record->setValues(array('period_start'=>$period_start,'period_end'=>$period_end));
//			$check = $payroll_period_record->save(null, true); //---This one uses permissions. Need to use this one!!

				
			$msg = "The payroll period from $period_start to $period_end has been created. Click here to proceed to ...";
			header('Location: index.php?-action=generate_payroll'.'&--msg='.urlencode($msg)); //Go to dashboard.
		//	header('Location: index.php?-action=dashboard'.'&--msg='.urlencode($msg)); //Go to dashboard.
		}
	}
}

?>