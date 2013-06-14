<?php

/*
	Add action to quickly pull timeclock issues? Potential future feature.
*/

class actions_timeclock {
	
	function handle(&$params){

		//Permission check
		//NONE

		$loginout = $_POST['loginout'];
		$logged = $_POST['logged'];
		
		//Get the application instance - used for pulling the query data
		$app =& Dataface_Application::getInstance();

		//Extend the limit of df_get_records_array past the usual first 30 records
		$query =& $app->getQuery();
		$query['-skip'] = 0;
		$query['-limit'] = ""; //******I think this will do *all*, but need to double check********
		
		
		//If the "Login" or "Logout" button has been pressed, load up the employees with Timeclock Passwords.
		if($loginout != ''){
			//Clear Variables
			$employees = array();
			$i = 0;

			//Set the query to pull the desired records
			$query['timeclock_pw'] = "!=";

			//Pull all desired records
			$records = df_get_records_array('employees', $query);

			//Unset the query
			unset($query['timeclock_pin']);

			//Run through all pulled records
			foreach($records as $i=>$record){
				$employees[$i]['name'] = $record->val('first_name').' '.$record->val('last_name');
				$employees[$i]['id'] = $record->val('employee_id');
				$i++;
			}
		}
		
		//If a user has attempted to log in
		if($logged == "true"){
			$employee_id = $_POST['employee'];
			$passwd = $_POST['pwd'];

			if($employee_id>0){
				//Get the password from the employees table, we use mysql here b/c xataface blocks password fields.
				$tp_query = mysql_query("SELECT timeclock_pw FROM employees WHERE employee_id=".$employee_id, df_db());
				$tp_record = mysql_fetch_array($tp_query);

				//Check if password is correct.
				if($passwd == $tp_record['timeclock_pw']){
					//Get the number of open logs
					$tc_query = mysql_query("SELECT log_id FROM time_logs WHERE employee_id=".$employee_id." AND callslip_id IS NULL AND start_time IS NOT NULL AND end_time IS NULL", df_db());
					$tc_rows = mysql_num_rows($tc_query);

					//Check if the user is logging in or out
					if($loginout == 'login'){

						//If no open records exists, or the bypass variable exists - create a new log entry (Successful Login)
						if($tc_rows == 0 || $_POST['bypass_error']==true){

							//Get the time 'now'
							$save_time = date('Y-m-d H:i');
							
							//Create new timelog record
							mysql_query("INSERT INTO time_logs (employee_id, category, start_time) VALUES ('$employee_id', 'OH', '$save_time')", df_db());

							//Clear Errors and the Selected Employee
							$error = '';
							$selected_employee = '';
							
							//Check to see if there are old open logs that were never closed
							if($tc_rows > 0)
								$error .= 'Reminder: You have '.($tc_rows).' open time logs. Please go see [someone] ASAP to correct your time log.<br>';

							//Check to see if there are old logs that don't have open times.
							$tc_query = mysql_query("SELECT log_id FROM time_logs WHERE employee_id=".$employee_id." AND callslip_id IS NULL AND start_time IS NULL AND end_time IS NOT NULL", df_db());
							$tc_rows = mysql_num_rows($tc_query);
							if($tc_rows > 0)
								$error .= 'Reminder: You have '.($tc_rows).' time logs with no login times. Please go see [someone] ASAP to correct your time log.<br>';

							
							//Set the loginout variable to 'save'
							$loginout='login_save';
						}
						//If a record already exists - Display warning msg, and create a bypass variable to allow the user to login (Failed Login)
						else{
							$error =	'It appears that you are already logged in, or have previously opened time logs.<br>
										If you meant to logout, <a href="index.php?-action=timeclock">click here</a> to return to the main screen, and select "Logout".<br><br>
										To bypass this error and continue logging in, retype your password and press the login button again.<br>
										You will need to see [someone] ASAP to correct your time log.
										<input type="hidden" name="bypass_error" value="true">';
						}
					}
					elseif($loginout == 'logout'){
						//Check for last login to see if it is open or not.
						$tc_last_query = mysql_query("SELECT MAX(log_id) AS log_id FROM time_logs WHERE employee_id=".$employee_id." AND callslip_id IS NULL", df_db());
						$tc_last_record = mysql_fetch_array($tc_last_query);
						$last_log_id = $tc_last_record['log_id'];

						$tc_last_query_end = mysql_query("SELECT end_time FROM time_logs WHERE log_id=".$last_log_id, df_db());
						$tc_last_record_end = mysql_fetch_array($tc_last_query_end);
						$last_end_time = $tc_last_record_end['end_time'];

						//If the last entry end_time is null AND there is a previous record (user has logged into the system before), close open log entry (Successful Logout)
						if($last_end_time == NULL && $last_log_id != NULL){
							//Get the time 'now'
							$save_time = date('Y-m-d H:i');

							//Add end_time to the timelog record
							mysql_query("UPDATE time_logs SET end_time = \"$save_time\" WHERE log_id = ".$tc_last_record['log_id'], df_db());
							
							//Clear Error and Selected Employee variables
							$error = '';
							$selected_employee = '';

							//Check to see if there are old open logs that were never closed
							if($tc_rows > 1)
								$error .= 'Reminder: You have '.($tc_rows - 1).' additional open time logs. Please go see [someone] ASAP to correct your time log.<br>';

							//Check to see if there are old logs that don't have open times.
							$tc_query = mysql_query("SELECT log_id FROM time_logs WHERE employee_id=".$employee_id." AND callslip_id IS NULL AND start_time IS NULL AND end_time IS NOT NULL", df_db());
							$tc_rows = mysql_num_rows($tc_query);
							if($tc_rows > 0)
								$error .= 'Reminder: You have '.($tc_rows).' time logs with no login times. Please go see [someone] ASAP to correct your time log.<br>';
							
							//Set the loginout variable to 'save'
							$loginout='logout_save';

						}
						//If the bypass error variable exists, create new log (Successful Logout)
						elseif($_POST['bypass_error']==true){
							//Get the time 'now'
							$save_time = date('Y-m-d H:i');
							
							//Create new timelog record
							mysql_query("INSERT INTO time_logs (employee_id, category, end_time) VALUES ('$employee_id', 'OH', '$save_time')", df_db());

							//Set Error Msg
							$error = 'A log has been created with no login time, please remember that you will need to see [someone] ASAP to correct your time log.';
							
							//Clear the Selected Employee variable
							$selected_employee = '';
							
							//Set the loginout variable to 'save'
							$loginout='logout_save';
						}
						//If the last record does not have a NULL end_time OR a previous record does not exist (user has never logged into the system before). (Failed Logout)
						elseif($last_end_time != NULL || $last_log_id == NULL){
							$error =	'You are trying to logout, but it appears that you have either already done so, or never logged in.<br>
										If you meant to login instead, <a href="index.php?-action=timeclock">click here</a> to return to the main screen, and select "Login".<br><br>
										To bypass this error and continue logging out, retype your password and press the logout button again.<br>
										This will create a log with no login time, and you will need to see [someone] ASAP to correct your time log.
										<input type="hidden" name="bypass_error" value="true">';
						}
					}
				}
				else
					$error = 'Incorrect Password. Please Try again.';
			}
			else
				$error = 'No Employee Selected';
		}
		
		
		$loginout = ucfirst($loginout);
		
		//Display the page
		df_display(array("employees"=>$employees,"loginout"=>$loginout,"error"=>$error,"selected_employee"=>$employee_id,"save_time"=>$save_time), 'timeclock.html');

	}
}


?>