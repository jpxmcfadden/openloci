	function valuelist__county_list(){
		$cs = array();

		$reco = df_get_record('admin_rate_codes', array('rate_id'=>2)); //EDIT THIS SO NOT "2"
		//$vals = $reco->val('rate_data');
		//echo $vals[0]['type'];
		
		foreach ( $reco->val('rate_data') as $vals){
		//	echo $vals['type'] . ' - ' . $vals['rate'] . '</br>';
			$cs[] = $vals['type'];
		};
		//echo "\n";
		
		return $cs;
	}
