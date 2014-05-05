<?php

class actions_call_slip_print_work_order {
	
	function handle(&$params){

		$app =& Dataface_Application::getInstance();
		$query =& $app->getQuery();

		//Load CSS
		echo '<link rel="stylesheet" href="style.css" type="text/css"/>';
		echo '<link rel="stylesheet" href="print.css" type="text/css" media="print" />';
		
		if(isset($query['-recordid'])){
			$record =& $app->getRecord();
			echo '<div class="print">' . getHTMLReport($record,6) . '</div>';
			
			//Update record status (only if status is NCO)
			if($record->val('status') == "NCO"){
				$record->setValue('status',"NCP"); //Set status to "Not Complete - Work Order Printed".
				$res = $record->save(null, true); //Save w/ permission check
			}

			//Auto Print
			print '<script type="text/javascript">window.print();</script>';

			//Redirect back to the previous page
			$url = $app->url('-action=browse') . '&--msg='.urlencode('Work Order(s) Printed.');;
		}
		else{
			$query['status'] = "NCO";
			$records = df_get_records_array("call_slips",$query);

			if(!empty($records)){
				foreach ($records as $record){
					echo '<div class="print">' . getHTMLReport($record,6) . '</div>';

					//Update record status
					$record->setValue('status',"NCP"); //Set status to "Not Complete - Work Order Printed".
					$res = $record->save(null, true); //Save w/ permission check
				}

				$msg = 'Work Order(s) Printed.';
				
				//Auto Print
				print '<script type="text/javascript">window.print();</script>';
			}
			else
				$msg = 'Nothing to print.';
			
			//Redirect back to the previous page
			$url = 'index.php?--msg='.urlencode($msg);
		}

		//Redirect back to previous
		print '<script type="text/javascript">window.location.replace("'.$url.'");</script>';
		
	}
}




/*		//If page has been submitted, GET the month/date
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
			
		//If the page has been submitted, create the invoices for printing.
		if ($month && $year){

			//Overwrite $cur_date[2]/[3] with the previously selected data
			//-- this is purely so that it shows up the same after "printing", so as not to freak out the user, making them think they selected the wrong month.
			$cur_date[2] = $month;
			$cur_date[3] = $year;
		
			//Load custom print CSS, this will hide everything except what will be created below.
			$app =& Dataface_Application::getInstance();
			$app->addHeadContent('<link rel="stylesheet" href="print.css" type="text/css" media="print" />');

			//Pull all records that coorespond to the selected month billing cycle.
			$recs = df_get_records_array('contracts', array('billing_cycle'=>$month));
			//^^   *******ADD QUERY TO MAKE > 30 RECORDS*******   ^^// -- See: http://xataface.com/forum/viewtopic.php?f=4&t=7176

			
			foreach($recs as $rec){
				//Pull all relevant information
				$customer = df_get_record('customers', array('customer_id'=>$rec->val('customer_id')));
				$customer = $customer->vals();

				$site = df_get_record('customer_sites', array('site_id'=>$rec->val('site_id')));
				$site = $site->vals();
				
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

				$footer = 'This is the Footer';
				
				//Create a <div> container that will be hidden except for printing
				echo '<div class="print">';
					//print_r($rec->vals());
					//df_display(array('records'=>$recs), 'contract_invoices.html');
					$c_add = company_address();

					echo	'<div class="invoice">'
					.			'<table>'
					.				'<tr>'
					.					'<td style="width: 4in;">'
					.							'<span style="font-size: 28px;"><b>' . company_name() . '</b></span>'
					.					'</td>'
					.					'<td></td>'
					.				'</tr><tr>'
					.					'<td style="vertical-align: top; height: 1in;">'
					.						$c_add['address'] . '<br>'
					.						$c_add['city'] . ', ' . $c_add['state'] . ' ' . $c_add['zip'] . '<br>'
					.						'Phone: ' . company_phone() . '<br>'
					.						'Fax: ' . company_fax()
					.					'</td><td style="vertical-align: top;">'
					//.						'<b>PREVENTATIVE MAINTENANCE BILLING</b>'
					.						'<b>INVOICE</b>'
					.						'<table><tr>'
					.							'<td style="width: 1.5in;">'
					.								'Invoice #'
					.							'</td><td>'
					.								'12345'
					.							'</td></tr><tr><td>'
					.								'Invoice Date'
					.							'</td><td>'
					.								date('m-d-Y')
					.							'</td>'
					.						'</tr></table>'
					.					'</td>'
					.				'</tr><tr>'
					.					'<td style="vertical-align: top; height: 1in;">'
					.						'BILL TO: ' . $customer['customer'] . '<br>'
					.						$customer['billing_address'] . '<br>'
					.						$customer['billing_city'] . ', ' . $customer['billing_state'] . ' '. $customer['billing_zip']
					.					'</td>'
					.					'<td style="vertical-align: top;">'
					.						'For Service Performed At: <br>'
					.						$site['site_address'] . '<br>'
					.						$site['site_city'] . ', ' . $site['site_state'] . ' '. $site['site_zip']
					.					'</td>'
					.				'</tr>'
					.			'</table>'
					.			'<br>'
					.			'<table>'
					.				'<tr>'
					.					'<td style="width: 4in;">'
					.						'<b><u>DESCRIPTION OF WORK</u></b>'
					.					'</td>'
					.				'</tr><tr>'
					.					'<td>'
					.						'Preventative Maintenance<br>'
					.						'<b>For the period:</b> ' . $month . ' ' . $year . ' through ' . $next_invoice . '.'
					.					'</td>'
					.				'</tr><tr>'
					.					'<td></td><td style="height: 1in; vertical-align: bottom;">'
					.						'<b>TOTAL:</b> $' . $contract_period_amt
					.					'</td>'
					.				'</tr>'
					.			'</table>'
					.			'<br><br><table><tr><td>' . $footer . '</td></tr></table>'
					.		'</div>'
					;
					
					
					
					echo "<br><br><br><br>";
				//Close the hidden container
				echo '</div>';
			}
			
			
			//Use javascript to pull up the print dialog
			print '<script type="text/javascript">window.print();</script>';
			
		}
		
		//Display the template page. Do no display for printing.
		echo '<div class="no_print">';
			df_display(array('cur_date'=>$cur_date), 'contract_invoices_select.html');
		echo '</div>';
		
*/
?>