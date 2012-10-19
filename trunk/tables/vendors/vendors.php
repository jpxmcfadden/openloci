<?php

class tables_vendors {

	//function getTitle(&$record){
	//	return $record->val('vendor');
	//}

	//function titleColumn(){
	//	return 'address';
	//}
	
	function physical_state__default(){
		return "FL";
	}

	function tax_id__validate( &$record, $value, $params=array()){
		if( !$value && ($record->_values['rec_1099'][0] == 1) ){
		//if( !$value ){
			echo "<script language=javascript>alert('ERROR: You have selected \"Requires 1099\", but provided no Tax ID / SSN.')</script>";
            //$params['message'] = "Sorry, this is an invalid.";
            return false;
		}

		return true;
	}

}
?>