<?php

class tables_payroll_deductions {

	function valuelist__repeat_list(){
		$payroll_config = df_get_record('_payroll_config', array('config_id'=>1));
				
		if($payroll_config->val('payroll_period') == "weekly")
			$list = array("All"=>"Every Payroll Period","1"=>"First Payroll Period of the Month","2"=>"Second Payroll Period of the Month","3"=>"Third Payroll Period of the Month","4"=>"Fourth Payroll Period of the Month");
		else if($payroll_config->val('payroll_period') == "2weeks")
			$list = array("All"=>"Every Payroll Period","1"=>"First Payroll Period of the Month","2"=>"Second Payroll Period of the Month");
		else if($payroll_config->val('payroll_period') == "bimonthly")
			$list = array("All"=>"Every Payroll Period","1"=>"First Payroll Period of the Month","2"=>"Second Payroll Period of the Month");
		else
			$list = array("All"=>"Every Payroll Period");
		//Return the type as per the list.
		return $list;
	}

}
?>
