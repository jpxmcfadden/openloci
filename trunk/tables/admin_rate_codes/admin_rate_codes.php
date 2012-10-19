<?php

class tables_admin_rate_codes {

	function rate_data__renderCell(&$record){
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
	
	
}

?>