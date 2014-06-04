<?php

class actions_call_slip_print_invoice {


	function handle(&$params){

		//Permission check
		if ( !isUser() )
			return Dataface_Error::permissionDenied("You are not logged in");

		$app =& Dataface_Application::getInstance();
		$query =& $app->getQuery();

		//Load CSS
		echo '<link rel="stylesheet" href="style.css" type="text/css"/>';
		echo '<link rel="stylesheet" href="print.css" type="text/css" media="print" />';
		
		//If one record is directly selected
		if(isset($query['-recordid'])){
			$record =& $app->getRecord();

			//Cost = T), Quoted = QU / SW / PM
			if($record->val('type') == 'TM')
				echo '<div class="print">' . getHTMLReport($record,'call_slip_invoice_cost') . '</div>';
			else
				echo '<div class="print">' . getHTMLReport($record,'call_slip_invoice_quote') . '</div>';
			
			//Update record status (only if status is RDY)
			if($record->val('status') == "RDY"){
				$record->setValue('status',"SNT"); //Set status to "Not Complete - Work Order Printed".
			//	$res = $record->save(null, true); //Save w/ permission check
				$res = $record->save(); //temp fix
			}

			//Auto Print
			print '<script type="text/javascript">window.print();</script>';

			//Redirect back to the previous page
			$url = $app->url('-action=browse') . '&--msg='.urlencode('Invoice(s) Printed.');;

			//Redirect back to previous
			print '<script type="text/javascript">window.location.replace("'.$url.'");</script>';
		}
		//If multiple records are selected
		else{
			$query['status'] = "RDY";
			$records = df_get_records_array("call_slips",$query);

			if(!empty($records)){
				foreach ($records as $record){
					//Cost = T), Quoted = QU / SW / PM
					if($record->val('type') == 'TM')
						echo '<div class="print">' . getHTMLReport($record,'call_slip_invoice_cost') . '</div>';
					else
						echo '<div class="print">' . getHTMLReport($record,'call_slip_invoice_quote') . '</div>';

					//Update record status (only if status is RDY)
					if($record->val('status') == "RDY"){
						$record->setValue('status',"SNT"); //Set status to "Not Complete - Work Order Printed".
					//	$res = $record->save(null, true); //Save w/ permission check
						$res = $record->save(); //temp fix
					}
				}

				$msg = 'Work Order(s) Printed.';
				
				//Auto Print
				print '<script type="text/javascript">window.print();</script>';
			}
			else
				$msg = 'Nothing to print.';
			
			//Redirect back to the previous page
			$url = 'index.php?--msg='.urlencode($msg);
			
			//Redirect back to previous - javascript like above is causing the print window to not appear for some reason
			echo '<meta http-equiv="refresh" content="0; url='.$url.'" />';
		}
		
	}
}






/*	
	function handle(&$params){
	
	
	//$app =& Dataface_Application::getInstance();
	//$query =& $app->getQuery();
	//$query['status'] = 'RDY';
	//print_r($query);
	
		//Permission check
		if ( !isUser() )
			return Dataface_Error::permissionDenied("You are not logged in");
			//header('Location: index.php?-action=login_prompt'); //Go to dashboard.

		//Check if the form has been printed / status change confirmed / which invoices have been selected.
		$print_inv = isset($_GET['confirm_print']) ? $_GET['confirm_print'] : NULL;
		$status_change = isset($_GET['confirm_status']) ? $_GET['confirm_status'] : NULL;
		$status_yes = isset($_GET['status_yes']) ? $_GET['status_yes'] : NULL;
		$status_no = isset($_GET['status_no']) ? $_GET['status_no'] : NULL;
		$invoice_selected = isset($_GET['invoice_selected']) ? $_GET['invoice_selected'] : NULL; //This is for data persistance

		//This will always be true, unless a user selects "no" in the confirmation popup for either option on the Status Change page. In that case, we don't need to preload all this data.
		if(!isset($status_change) && !isset($status_no) && !isset($status_no)){
		
			//Load custom print CSS
			$app =& Dataface_Application::getInstance();
			$app->addHeadContent('<link rel="stylesheet" href="print.css" type="text/css" media="print" />');

			//Extend the limit of df_get_records_array past the usual first 30 records, and query the records where [status == "RDY"]
			$query =& $app->getQuery();
			$query['-skip'] = 0;
			$query['-limit'] = 10000;
			$query['status'] = 'RDY';

			//Pull all records that coorespond to the selected month billing cycle.
			//$records = df_get_records_array('call_slips', array('status'=>'RDY'));
			$records = df_get_records_array('call_slips', $query);


			//Load unchanging variables
			$c_add = company_address();
			$invoices = '';
			
			//Create blank variables
			$invoice_headers = array();
			$invoice_selected = '';

			//Generate invoices / headers for all pulled records
			foreach($records as $i=>$record){

				//Pull all relevant information
				$customer = df_get_record('customers', array('customer_id'=>$record->val('customer_id')));
				$customer = $customer->vals();

				$site = df_get_record('customer_sites', array('site_id'=>$record->val('site_id')));
				$site = $site->vals();
				
				$type = $record->val('type');

				$invoice_headers[$i]['id'] = $record->val('call_id');
				$invoice_headers[$i]['customer'] = $customer['customer'];
				$invoice_headers[$i]['site'] = $site['site_address'];

				//Create invoices on print confirm
				if($print_inv == "yes" && isset($_GET[$record->val('call_id')]) && $_GET[$record->val('call_id')]=="on"){

					//Save the selected invoices into a comma separated string - for use later with status changes
					$invoice_selected .= $record->val('call_id').',';
					
					//For Contracts calculate billing period
					if($type == "PM"){
						//Pull the contract record
						$contract = df_get_record('contracts', array('contract_id'=>$record->val('contract_id')));
						$contract = $contract->vals();
					
						//Get the current invoice period
						$month = date('F', strtotime($record->strval('call_datetime')));
						$year = date('Y', strtotime($record->strval('call_datetime')));
					
						//Get next invoice period
						$inv_months = $contract['billing_cycle'];
						foreach($inv_months as $i=>$nextinvoice){
							if($nextinvoice == $month){
								if(sizeof($inv_months)-1 == $i)
									$next_invoice = $inv_months[0] . ' ' . ($year+1);
								else
									$next_invoice = $inv_months[$i+1] . ' ' . $year;
								break;
							}
						}
					}

					//Generic Footer - Change per type?
					$footer = 'This is the Footer';

					//Create a <div> container that will be hidden except for printing
					$invoices .= '<div class="print">';
					
							//Build the invoice pages.
							$invoices .=
									'<div class="invoice">'
							.			'<table border=0>'
							.				'<tr>'
							.					'<td style="width: 4in;">'
							.							'<span style="font-size: 28px;"><b>' . company_name() . '</b></span>'
							.							'<hr>'
							.					'</td>'
							.					'<td></td>'
							.				'</tr><tr>'
							.					'<td style="vertical-align: top; height: 1in;">'
							.						$c_add['address'] . '<br>'
							.						$c_add['city'] . ', ' . $c_add['state'] . ' ' . $c_add['zip'] . '<br>'
							.						'Phone: ' . company_phone() . '<br>'
							.						'Fax: ' . company_fax()
							.					'</td><td style="vertical-align: top;">'
							.						'<b>INVOICE</b>'
							.						'<table><tr>'
							.							'<td style="width: 1.5in;">'
							.								'Invoice #'
							.							'</td><td>'
							.								$record->val('type') . $record->val('call_id')
							.							'</td></tr><tr><td>'
							.								'Invoice Date'
							.							'</td><td>'
							.								date('m/d/Y')
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
							.			'<table border=0 style="width: 8in;">'
							.				'<tr>'
							.					'<td>'
							.						'<b><u>DESCRIPTION OF WORK</u></b>'
							.					'</td>'
							//.					'<td></td>'
							.				'</tr>'
							;

							if($type == "PM"){
								$billm = billing_materials($record,1);
								$invoices .= 	'<tr>'
								.					'<td style="padding: 10px;">'
								.						'Preventative Maintenance<br>'
								.						'<b>For the period:</b> ' . $month . ' ' . $year . ' through ' . $next_invoice . '.'
								.					'</td>'
								.				'</tr><tr>'
								.					'<td style="padding: 10px;">'
								.						$billm['table'] . '<br>'
								.						'<div class="invoice_div">'
								.							'<u>TOTAL: <b>$' . $record->val('quoted_cost') . '</b></u>'
								.						'</div>'
								.					'</td>'
								.				'</tr>'
								;
							}
							elseif($type == "TM"){
								$billh = billing_hours($record);
								$billm = billing_materials($record);
								$invoices .= 	'<tr>'
								.					'<td>'
								.						'<p>' . $record->val('tech_notes') . '<p>'
								.					'</td>'
								.				'</tr><tr>'
								.					'<td style="padding: 10px;">'
								.						$billh['table'] . '<br>'
								.						$billm['table'] . '<br>'
								.						'<div class="invoice_div">'
								.							'<u>TOTAL: <b>$' . number_format($billh['total'] + $billm['total'],2) . '</b></u>'
								.						'</div>'
								.					'</td>'
								.				'</tr>'
								;
							}
							elseif($type == "QU"){
								$billm = billing_materials($record,1);
								$invoices .= 	'<tr>'
								.					'<td>'
								.						'<p>' . $record->val('tech_notes') . '<p>'
								.					'</td>'
								.				'</tr><tr>'
								.					'<td style="padding: 10px;">'
								.						$billm['table'] . '<br>'
								.						'<div class="invoice_div">'
								.							'<u>TOTAL: <b>$' . $record->val('quoted_cost') . '</b></u>'
								.						'</div>'
								.					'</td>'
								.				'</tr>'
								;
							}	
							$invoices .= ''
							.			'</table>'
							.			'<br><br><table><tr><td>' . $footer . '</td></tr></table>'
							.		'</div>'
							;

						//Close the hidden container
						$invoices.= '</div>';
				}

			}

		}
		
		//If the print button is pressed. 
		if($print_inv == "yes"){
			//Javascript to pull up the print dialog
			$print_script = '<script type="text/javascript">window.print();</script>';
			
			//Display the Invoice Status Change page, and make it hidden for printing.
			echo '<div class="no_print">';
				//f_display(array("invoices"=>$invoices, "print_script"=>$print_script), 'invoice_change_status.html');
				df_display(array("print_script"=>$print_script,"invoice_selected"=>$invoice_selected), 'invoice_change_status.html');
			echo '</div>';

			//Put the invoice data at the bottom of the page
			//	*This isn't the nicest way to do this, but it's effective.*
			//	*Pushing the invoice and print_script variables into the template was causing lots of issues.*
			echo $invoices;
		}
		//If "no" is selected in the confirmation popup for either option on the Status Change page.
		elseif($status_change == "no"){
				df_display(array("invoice_selected"=>$invoice_selected), 'invoice_change_status.html');
		}
		//If the status change Yes button is pressed: Perform the status change and redirect back to the dashboard.
		elseif($status_change == "yes" && isset($status_yes)){

			//Create an array from the saved comma separated string - slice off the last element which is created b/c of the extraneous comma
			$invoice_selected=array_slice(explode(",",$invoice_selected),0,-1);

			//Pull all records that have been selected and change their status to Printed / Sent (SNT).
			foreach($invoice_selected as $id){
				$record = df_get_record('call_slips', array('call_id'=>$id));
				$record->setValue('status',"SNT");
				$record->save();
			}
		
			//Redirect back to the dashboard
			$msg = "The selected call slips have changed to status \"Printed / Sent\".";
			header('Location: index.php?-action=dashboard'.'&--msg='.urlencode($msg)); //Go to dashboard.
		}
		//If the status change No button is pressed: Redirect back to the dashboard.
		elseif($status_change == "yes" && isset($status_no)){
			//Redirect back to the dashboard
			$msg = "The selected call slips have not changed status.";
			header('Location: index.php?-action=dashboard'.'&--msg='.urlencode($msg)); //Go to dashboard.
		}
		//Start here. Display table with all call slips ready to print.
		else{
			df_display(array("invoice_headers"=>$invoice_headers), 'invoice.html');
		}
		
		
	}

}

function billing_materials(&$record, $nc = NULL){

		$childString = "";
		
		//Materials
			$childString .= '<b><u>Materials</u></b><br><br>';
			$childString .= '<table class="invoice_tab"><tr>
								<th>Item</th>
								<th style="text-align: right">Quantity</th>';
			
			if($nc!=1)
				$childString .=		'<th style="text-align: right">Cost</th>'
								.	'<th style="text-align: right">Total Cost</th>';
								
			$childString .= '</tr>';

			$total_materials_cost_sale = 0;

			$purchaseorderRecords = $record->getRelatedRecords('call_slip_purchase_orders');
			foreach ($purchaseorderRecords as $cs_pr){
				$subtotal_sale = $cs_pr['cost_sale'] * $cs_pr['quantity'];

				$childString .= '<tr>' .
									'<td>' . $cs_pr['item_name'] .
									'</td><td style="text-align: right">' . $cs_pr['quantity'];
									
				if($nc!=1)
					$childString .= '</td><td style="text-align: right">' . $cs_pr['cost_sale'] .
									'</td><td style="text-align: right">' . number_format($subtotal_sale,2);
									
				$childString .=		'</td>' . 
								'</tr>';
				
				$total_materials_cost_sale += $subtotal_sale;
			}
		
			$inventoryRecords = $record->getRelatedRecords('call_slip_inventory');
			foreach ($inventoryRecords as $cs_ir){
				//Pull the item name / cost out of the 'inventory' table
				$rec = df_get_record('inventory', array('inventory_id'=>$cs_ir['inventory_id']));

				$subtotal_sale = $cs_ir['sale_price'] * $cs_ir['quantity'];

				$childString .= '<tr>' .
									'</td><td>' . $rec->display('item_name') .
									'</td><td style="text-align: right">' . $cs_ir['quantity'];
				if($nc!=1)
					$childString .=	'</td><td style="text-align: right">' . $cs_ir['sale_price'] .
									'</td><td style="text-align: right">' . number_format($subtotal_sale,2);
									
				$childString .=		'</td>' .
								'</tr>';

				$total_materials_cost_sale += $subtotal_sale;
			}
			
			if($nc!=1)
				$childString .= '<tr><td></td><td></td><td>' .
								'<td style="text-align: right"><b>' . number_format($total_materials_cost_sale,2) . '</b></td>' .
								'</td></tr>';

			$childString .= '</table><br>';

	return array('table'=>$childString,'total'=>$total_materials_cost_sale);
}


function billing_hours(&$record){
	$childString = '';


	//Hours Worked
		$childString .= '<b><u>Hours</u></b><br><br>';
		$childString .= '<table class="invoice_tab"><tr>
							<th>Date</th>
							<th>Description</th>
							<th style="text-align: right">Hours</th>
							<th style="text-align: right">Rate</th>
							<th style="text-align: right">Cost</th>
						</tr>';

		$total_time_cost = 0;

		$employeeRecords = $record->getRelatedRecords('time_logs');
		foreach ($employeeRecords as $cs_er){

			$tech_desc = array(	"tech_rt"=>"Technician - Regular Time",
								"tech_ot"=>"Technician - Overtime",
								"tech_tt"=>"Technician - Travel Time",
								"help_rt"=>"Helper - Regular Time",
								"help_ot"=>"Helper - Overtime",
								"help_tt"=>"Helper - Travel Time",
								"supr_rt"=>"Supervisior - Regular Time",
								"supr_ot"=>"Supervisior - Overtime",
								"supr_tt"=>"Supervisior - Travel Time",
								"no_chrg"=>"No Charge",
								"custom" =>"Custom"
								);
			

			$arrive = Dataface_converters_date::datetime_to_string($cs_er['start_time']);
			$depart = Dataface_converters_date::datetime_to_string($cs_er['end_time']);

			$ldate = date_parse($arrive);
			$hours = number_format(((strtotime($depart) - strtotime($arrive)) / 3600),1);
			$cost = number_format($cs_er['rate_per_hour'] * $hours,2);
			$total_time_cost += $cost;

			$childString .= '<tr><td>' . $ldate['month'] . '/' . $ldate['day'] . '/' . $ldate['year'] .
								'</td><td>' . $tech_desc[$cs_er['rate_type']] .
								'</td><td style="text-align: right">' . $hours .
								'</td><td style="text-align: right">' . $cs_er['rate_per_hour'] . '/hr' .
								'</td><td style="text-align: right">' . $cost .
								'</td>' .
							'</tr>';
		}
		$childString .= '<tr><td></td><td></td><td></td><td></td><td style="text-align: right"><b>' . number_format($total_time_cost,2) . '</b></td></tr>';
		$childString .= '</table><br>';

	return array('table'=>$childString,'total'=>$total_time_cost);
}

*/
?>
