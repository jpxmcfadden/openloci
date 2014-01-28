<?php

class tables_payroll_period {

	function getTitle(&$record){
		return "Payroll Period: " . $record->val('payroll_start').' to '.$record->val('payroll_end');
	}

	function beforeSave(&$record){
	}

	function afterSave(&$record)
	{
	}
	
}
?>