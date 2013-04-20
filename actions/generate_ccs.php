<?php
class actions_generate_ccs {
	function handle(&$params){
		//If page has been submitted, GET the month/date
		$month = $_GET['month'];
		$year = $_GET['year'];

		//Create the appropriate variables to pass to, and display the template page.

			//$cur_date[0] = Month List, $cur_date[1] = Year List
			$cur_date[0] = array("January","February","March","April","May","June","July","August","September","October","November","December");
			for($i = 2000; $i <=2050; $i++){
				$cur_date[1][$i] = $i;
			}

			//$cur_date[2] = Next Month, $cur_date[3] = The Current Year, unless next month is January, in which case, it will be Next Year
			$cur_date[2] = date("m")+1;
			$cur_date[3] = date("Y");
		
			if($cur_date[2] > 12){
				$cur_date[2] = 1;
				$cur_date[3] += 1;
			}
			
			//Make the month show up as full text (quick and dirty using the already established array, w/o "DateTime")
			$cur_date[2] = $cur_date[0][$cur_date[2]-1];
			
		//If the page has already been submitted, create the call slips.
		if ($month && $year){
			
			//Pull all records that coorespond to the selected month billing cycle.
			$recs = df_get_records_array('contracts', array('billing_cycle'=>$month));
			//^^   *******ADD QUERY TO MAKE > 30 RECORDS*******   ^^// -- See: http://xataface.com/forum/viewtopic.php?f=4&t=7176


			//echo "$month - $year <br><br>";

			
			foreach($recs as $rec){
				//Pull all relevant information
				//$customer = df_get_record('customers', array('customer_id'=>$rec->val('customer_id')));
				//$customer = $customer->vals();

				//$site = df_get_record('customer_sites', array('site_id'=>$rec->val('site_id')));
				//$site = $site->vals();
				
				//Get next invoice period
				$inv_months = $rec->val('billing_cycle');
				foreach($inv_months as $i=>$nextinvoice){
					if($nextinvoice == $month){
						if(sizeof($inv_months)-1 == $i)
							$next_invoice = $inv_months[0] . ' ' . ($year+1);
						else
							$next_invoice = $inv_months[$i+1] . ' ' . $year;
						break;
					}
				}

				//Calculate charge per period
				$contract_period_amt = number_format(round($rec->val('contract_amount') / sizeof($inv_months), 2), 2);
				
				//echo $customer['customer'] . ' - ' . $site['site_address'] . ' : ' . $contract_period_amt . '<br>';
				$cs_record = new Dataface_Record('call_slips', array());
				$cs_record->setValues(
										array(
											'customer_id'=>$rec->val('customer_id'),
											'site_id'=>$rec->val('site_id'),
											'quoted_cost'=>$contract_period_amt,
											'type'=>'PM','status'=>'NCO',
											'call_datetime'=>date('Y-m-d g:i A', strtotime("1 $month $year")),
											'call_instructions'=>$rec->val('instructions'),
											'contract_id'=>$rec->val('contract_id')
										)
									);
				$cs_record->save(null, true);

			}

			
			$msg = "The call slips for $month $year"."'s scheduled maintenance have been created.";
			header('Location: index.php?-action=dashboard'.'&--msg='.urlencode($msg)); //Go to dashboard.

		}

		//If the page has not been submitted, display the selection page.
		else{
			df_display(array('cur_date'=>$cur_date), 'generate_ccs.html');
		}
		
		//Display the template page. Do no display for printing.
		//echo '<div class="no_print">';
		//echo '</div>';
	}
}

?>