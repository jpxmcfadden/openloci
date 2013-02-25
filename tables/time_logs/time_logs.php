<?php

class tables_time_logs {
	private $rate_type;

	function rate_id__default(){
		return 1;
	}

	function rate_type__validate(&$record, $value, &$params){
		$this->rate_type = $value;
		return 1;
	}
	
	function beforeSave(&$record){
		if($this->rate_type == 'no_chrg')
			$record->setValue('rate_per_hour', 0.00);
		else{
			$rate_rec = df_get_record('rates', array('rate_id'=>$record->val('inventory_id')));
			$record->setValue('rate_per_hour', $rate_rec->val($this->rate_type));
		}
	}
	
}

?>